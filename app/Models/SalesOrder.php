<?php

namespace App\Models;

use App\Domain\Contracts\DocumentCounterContract;
use App\Domain\Contracts\StockContract;
use App\Domain\Data\DocumentCounterSetupData;
use App\Domain\ValueObjects\AdjustStockValue;
use App\Exceptions\AppException;
use App\Models\Base\BaseModel;
use App\Services\Gateway\Base\BaseServiceContract;
use App\Services\Gateway\Rest\RestService;
use App\Utils\DateTimeUtil;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesOrder extends MasterModel
{
    const URI = 'com.zuragan.sales_order';
    const NUMBERING_PREFIX = 'SO';
    const DRAFT = 'DRAFT';
    const AWAITING_PAYMENT = 'AWAITING_PAYMENT';
    const AWAITING_SHIPMENT = 'AWAITING_SHIPMENT';
    const FULFILLED = 'FULFILLED';
    const CANCELED = 'CANCELED';
    const SHIPPED = 'SHIPPED';

    protected $table = 'sales_orders';
    protected $primaryKey = 'sales_order_id';

    protected $fillable = [
        'contact_id',
        'organization_id',
        'sales_order_number',
        'reference_number',
        'sales_order_date',
        'due_date',
        'invoice_date',
        'shipment_date',
        'discount_contact_id',
        'discount_amount_type',
        'discount_amount_value',
        'adjustment_name',
        'adjustment_value',
        'internal_notes',
        'term_and_condition',
        'customer_notes',
        'term_date',
        'sales_order_status',
        'invoice_email',
        'billing_address',
        'billing_region',
        'billing_district',
        'billing_province',
        'billing_country',
        'billing_zip',
        'billing_fax',
        'billing_phone',
        'billing_mobile',
        'shipping_address',
        'shipping_region',
        'shipping_district',
        'shipping_province',
        'shipping_country',
        'shipping_zip',
        'shipping_fax',
        'tax_included',
        'shipping_weight',
        'shipping_weight_unit',
        'shipping_rate',
        'shipping_carrier_name',
        'shipping_carrier_code',
        'shipping_carrier_id',
        'shipping_carrier_service',
        'shipped_status',
        'paid_status',
        'my_sales_channel_id',
        'shipping_from_address',
        'shipping_from_region',
        'shipping_from_district',
        'shipping_from_province',
        'shipping_from_country',
        'shipping_from_zip',
        'shipping_from_fax',
        'shipping_phone',
        'shipping_mobile',
        'is_dropship',
    ];
    protected $appends = [
        'total',
        'sub_total',
        'tax',
        'shipping_charge',
        'invoice_status',
        'shipment_status',
        'is_overdue',
        'shipment_id',
        'shipments',
        'payments'
    ];
//    protected $hidden = ['sales_order_details'];
    protected $softDeleteCascades = ['sales_order_details'];

    protected $columnDefault = ['*'];
    protected $columnSimple = ['*'];

    private $stockService;
    private $counterService;

    public function __construct()
    {
        parent::__construct();

        $this->nestedBelongConfigs = [
            'my_sales_channel' => ['*'],
            "contact" => ["*"],
        ];

        $this->nestedHasManyConfigs = [
            "sales_order_details" => ['*'],
            "invoices" => ['*'],
        ];

        $this->stockService = app(StockContract::class);
        $this->counterService = app(DocumentCounterContract::class);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($so) {
            //validate payment already processed
            $invoices = $so->invoices;

            foreach ($invoices as $invoice) {
                if ($invoice->invoice_status == Invoice::VOID) {
                    continue;
                }
                $payments = $invoice->payments;
                if ($payments->count() > 0) {
                    throw new AppException(
                        "Failed to delete Sales Order. Delete all related payment " .
                        "to this Sales Order first",
                        Response::HTTP_UNPROCESSABLE_ENTITY
                    );
                }
            }
        });
    }

    public function my_sales_channel()
    {
        return $this->belongsTo(MySalesChannel::class)->with('sales_channel');
    }

    public function sales_order_details()
    {
        return $this->hasMany(SalesOrderDetail::class, 'sales_order_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'sales_order_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function dropship_contact()
    {
        return $this->belongsTo(Contact::class, 'dropship_contact_id', 'contact_id');
    }

    public function discount()
    {
        return $this->hasOne(DiscountContact::class, 'discount_contact_id');
    }

    public function packages()
    {
        return $this->hasMany(Package::class, 'sales_order_id');
    }

    //end relation
    public function getSalesOrderDateAttribute($v)
    {
        return DateTimeUtil::fromMicroSecond($v);
    }

    public function setSalesOrderDateAttribute($v)
    {
        $this->attributes['sales_order_date'] = empty($v) ? null : DateTimeUtil::toMicroSecond($v);
    }

    public function getShipmentDateAttribute($v)
    {
        return DateTimeUtil::fromMicroSecond($v);
    }

    public function setShipmentDateAttribute($v)
    {
        $this->attributes['shipment_date'] = empty($v) ? null : DateTimeUtil::toMicroSecond($v);
    }

    public function getTermDateAttribute($v)
    {
        return DateTimeUtil::fromMicroSecond($v);
    }

    public function setTermDateAttribute($v)
    {
        $this->attributes['term_date'] = empty($v) ? null : DateTimeUtil::toMicroSecond($v);
    }

    private function setupNumbering()
    {
        $setupData = new DocumentCounterSetupData(
            self::URI,
            self::NUMBERING_PREFIX
        );
        //make sure document counter is setup (won't setup if exist)
        $this->counterService->setup($setupData, false);
    }

    public function getShipmentIdAttribute()
    {

        $shipmentId = null;
        $package = $this->packages()->with('shipment')->first();

        if ($package) {
            Log::error("Package exist after delete shipment.");
            $shipmentId = isset($package->shipment->shipment_id) ? $package->shipment->shipment_id : null;
        }
        return $shipmentId;
    }

    public function getNextSalesOrderNumber($commit = true)
    {
        $this->setupNumbering();
        $nextId = $this->counterService->getNumbering(self::URI, $commit);
        return $nextId;
    }

    public function getShippingChargeAttribute()
    {
//        $shippingCharge = $this->shipping_rate * $this->shipping_weight;
        $shippingCharge = (float)$this->shipping_rate;

        //markup
        return round($shippingCharge);
    }

    public function getTotalAttribute($v)
    {
        $subTotal = $this->subTotal;

        $adjustment = (float)$this->adjustment_value;

        if ($this->discount_amount_type == 'percentage')
            $disc = ((float)$this->discount_amount_value / 100) * $subTotal;
        else if ($this->discount_amount_type == 'fixed')
            $disc = (float)$this->discount_amount_value;
        else
            $disc = 0;

        $shippingCharge = $this->shipping_charge;

        $total = $subTotal - $disc + $this->tax + $adjustment + $shippingCharge;

        //markup
        return round($total);
    }

    public function getSubTotalAttribute($v)
    {
        $subTotal = 0;
        if (!empty($this->sales_order_details)) {
            foreach ($this->sales_order_details as $k => $v) {
                $subTotal += $v->amount;
            }
        }

        //markup
        return round($subTotal);
    }

    public function getTaxAttribute()
    {
        return !$this->tax_included ? $this->subTotal * 0.1 : 0;
    }

    public function getDueDateAttribute($v)
    {
        return DateTimeUtil::fromMicroSecond($v);
    }

    public function setDueDateAttribute($v)
    {
        $this->attributes['due_date'] = empty($v) ? null :
            DateTimeUtil::toMicroSecond($v);
    }

    public function getInvoiceDateAttribute($v)
    {
        return DateTimeUtil::fromMicroSecond($v);
    }

    public function setInvoiceDateAttribute($v)
    {
        $this->attributes['invoice_date'] = empty($v) ? null :
            DateTimeUtil::toMicroSecond($v);
    }

    public function getInvoiceStatusAttribute()
    {
        $inv = $this->invoices()->select('invoice_id', 'invoice_status')->get();

        $invStats = $inv->map(function ($i) {
            return $i->invoice_status;
        });

        if (in_array(Invoice::DRAFT, $invStats->toArray(), true)) {
            return 'DRAFT';
        }

        if (in_array(Invoice::VOID, $invStats->toArray(), true)) {
            return 'VOID';
        }

        if (in_array(Invoice::PAID, $invStats->toArray(), true)) {
            return 'PAID';
        }

        if ($this->is_overdue) {
            return 'OVERDUE';
        }

        if (in_array(Invoice::PARTIALLY_PAID, $invStats->toArray(), true)) {
            return 'PARTIALLY_PAID';
        }

        return 'UNPAID';
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date < date("Y-m-d");
    }

    public function getShipmentStatusAttribute()
    {
        $data = $this->packages()->select('package_id')->whereHas('shipment')->get();

        return !$data->isEmpty() ? 'SHIPPED' : 'NOT_YET_SHIPPED';

    }

    public function getShipmentsAttribute()
    {
        $package = $this->packages()
            ->select('package_id')
            ->with(['shipment' => function ($q) {
                return $q->select(
                    'shipment_id',
                    'package_id',
                    'tracking_number',
                    'carrier_id'
                );
            }])->get();

        return isset($package) ? $package->map(function ($p) {
            return $p->shipment;
        }) : null;
    }

    public function getPaymentsAttribute()
    {
        $invoices = $this->invoices()
            ->with(['payments' => function ($q) {
                return $q->select(
                    'payment_id',
                    'invoice_id',
                    'reference_number',
                    'date',
                    'currency',
                    'amount',
                    'notes',
                    'payment_mode_id',
                    'payment_mode_name',
                    'payment_account_id',
                    'payment_account_holder',
                    'payment_account_name',
                    'payment_number',
                    'organization_id'
                );
            }])->get();

        return isset($invoices) ? $invoices->flatmap(function ($o) {
            return $o->payments;
        }) : null;
    }


    public function rules($id = null)
    {
        $forUpdate = $id ? ',' . $id . ',sales_order_id' : '';
        $orgId = $this->getOrganizationId();

        return array(
            'contact_id' => 'required|integer|exists:contacts,contact_id|in_organization:contacts,' . $orgId . ',contact_id',
            'organization_id' => 'required|integer',
            'sales_order_number' => 'required|string|max:50|org_unique:sales_orders,sales_order_number' . $forUpdate,
            'reference_number' => 'nullable|string|max:100',
            'sales_order_date' => 'required|numeric',
            'due_date' => 'nullable|numeric',
            'invoice_date' => 'required|numeric',
            'shipment_date' => 'nullable|numeric',           #|greater_than:sales_order_date', ##not working
            'discount_contact_id' => 'nullable|integer|exists:discount_contacts,discount_contact_id',
            'discount_amount_type' => 'nullable|required_with:discount_amount_value|string',
            'discount_amount_value' => 'nullable|numeric', //TODO(jee): saat ini tidak perlu depend on discount_contact_id
            'adjustment_name' => 'nullable|string|max:100',
            'adjustment_value' => 'nullable|numeric',
            'internal_notes' => 'nullable|string|max:500',
            'term_and_condition' => 'nullable|string',
            'customer_notes' => 'nullable|string|max:500',
            'term_date' => 'numeric',
            'sales_order_status' => 'required|string',
            'invoice_email' => 'nullable|string',
            'billing_address' => 'nullable|nullable|string|max:255',
            'billing_region' => 'nullable|integer',
            'billing_district' => 'nullable|integer',
            'billing_province' => 'nullable|integer',
            'billing_country' => 'nullable|integer',
            'billing_zip' => 'nullable|alpha_num|max:10',
            'billing_fax' => 'nullable|alpha_num|max:20',
            'billing_phone' => 'nullable|string|min:9|max:15|regex:/^\+?[^a-zA-Z]{5,}$/',
            'billing_mobile' => 'nullable|string|min:9|max:15|regex:/^\+?[^a-zA-Z]{5,}$/',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_region' => 'nullable|integer',
            'shipping_district' => 'nullable|integer',
            'shipping_province' => 'nullable|integer',
            'shipping_country' => 'nullable|integer',
            'shipping_zip' => 'nullable|alpha_num|max:10',
            'shipping_fax' => 'nullable|alpha_num|max:20',
            'tax_included' => 'boolean',

            'shipping_weight' => 'integer|min:0',
            'shipping_weight_unit' => 'required_with:shipping_weight|string|in:gr,kg',
            'shipping_rate' => 'numeric|between:0,9999999999',

            'shipping_carrier_name' => 'nullable|string',
            'shipping_carrier_code' => 'nullable|string',
            'shipping_carrier_id' => 'nullable|integer',
            'shipping_carrier_service' => 'nullable|string',

            'shipped_status' => 'nullable|integer|in:123',
            'paid_status' => 'nullable|integer|in:123',
            'my_sales_channel_id' => 'sometimes|nullable|integer|exists:my_sales_channels,id|in_organization:my_sales_channels,' . $orgId,

            'shipping_from_address' => 'nullable|string|max:255',
            'shipping_from_region' => 'nullable|integer',
            'shipping_from_district' => 'nullable|integer',
            'shipping_from_province' => 'nullable|integer',
            'shipping_from_country' => 'nullable|integer',
            'shipping_from_zip' => 'nullable|alpha_num|max:10',
            'shipping_from_fax' => 'nullable|alpha_num|max:20',
            'shipping_phone' => 'nullable|string|min:9|max:15|regex:/^\+?[^a-zA-Z]{5,}$/',
            'shipping_mobile' => 'nullable|string|min:9|max:15|regex:/^\+?[^a-zA-Z]{5,}$/',

            'is_dropship' => 'nullable|boolean',
        );
    }

    public function conditionalRules(&$v)
    {
        $orgId = $this->getOrganizationId();

        $v->sometimes('dropship_contact_id', [
                'exists:contacts,contact_id',
                'contact_type:' . Contact::STATUS_DROPSHIPPER,
                'in_organization:contacts,' . $orgId . ',contact_id',
            ]
            , function ($input) {
                return $input->is_dropship;
            });
    }

    public static function inst()
    {
        $me = new SalesOrder();
        return $me;
    }

    public function populate($request = [],
                             BaseModel $model = null)
    {
        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);

        $model->organization_id = intOrNull(AuthToken::info()->organizationId);
        $model->contact_id = intOrNull($req->get('contact_id'));
        $model->sales_order_number = $req->get('sales_order_number');
        $model->reference_number = $req->get('reference_number') ?? str_random(10);
        $model->sales_order_date = $req->get('sales_order_date');
        $model->invoice_date = $req->get('invoice_date');
        $model->due_date = $req->get('due_date') ?? $req->get('invoice_date');
        $model->shipment_date = $req->get('shipment_date');

        $model->discount_contact_id = intOrNull($req->get('discount_contact_id'));
        $model->discount_amount_type = $req->get('discount_amount_type');
        $model->discount_amount_value = (float)$req->get('discount_amount_value');
        $model->adjustment_name = $req->get('adjustment_name');
        $model->adjustment_value = (float)$req->get('adjustment_value');
        $model->term_date = $req->get('term_date');
        $model->internal_notes = $req->get('internal_notes');
        $model->term_and_condition = $req->get('term_and_condition');
        $model->customer_notes = $req->get('customer_notes');
        $model->sales_order_status = $req->get('sales_order_status');
        $model->invoice_email = $req->get('invoice_email');

        $model->billing_address = $req->get('billing_address');
        $model->billing_region = intOrNull($req->get('billing_region'));
        $model->billing_district = intOrNull($req->get('billing_district'));
        $model->billing_province = intOrNull($req->get('billing_province'));
        $model->billing_country = intOrNull($req->get('billing_country'));
        $model->billing_zip = $req->get('billing_zip');
        $model->billing_fax = $req->get('billing_fax');
        $model->billing_phone = $req->get('billing_phone');
        $model->billing_mobile = $req->get('billing_mobile');

        $model->shipping_address = $req->get('shipping_address');
        $model->shipping_region = intOrNull($req->get('shipping_region'));
        $model->shipping_district = intOrNull($req->get('shipping_district'));
        $model->shipping_province = intOrNull($req->get('shipping_province'));
        $model->shipping_country = intOrNull($req->get('shipping_country'));
        $model->shipping_zip = $req->get('shipping_zip');
        $model->shipping_fax = $req->get('shipping_fax');
        $model->shipping_phone = $req->get('shipping_phone');
        $model->shipping_mobile = $req->get('shipping_mobile');
        //TODO(): force true (sementara karna permintaan owner)
        //$model->tax_included = Setting::reFormatOutput()['web.item.price.tax_included'] ?? true;
        $model->tax_included = true;

        $model->shipping_weight = (float)$req->get('shipping_weight');
        $model->shipping_weight_unit = $req->get('shipping_weight_unit') ?? 'gr';
        $model->shipping_rate = (float)$req->get('shipping_rate');
        $model->shipping_carrier_id = intOrNull($req->get('shipping_carrier_id'));

        $model->shipping_carrier_code = $req->get('shipping_carrier_code');
        $model->shipping_carrier_name = $req->get('shipping_carrier_name');
        $model->shipping_carrier_service = $req->get('shipping_carrier_service');
        $model->my_sales_channel_id = $req->get('my_sales_channel_id');

        $model->shipping_from_address = $req->get('shipping_from_address');
        $model->shipping_from_region = intOrNull($req->get('shipping_from_region'));
        $model->shipping_from_district = intOrNull($req->get('shipping_from_district'));
        $model->shipping_from_province = intOrNull($req->get('shipping_from_province'));
        $model->shipping_from_country = intOrNull($req->get('shipping_from_country'));
        $model->shipping_from_zip = $req->get('shipping_from_zip');
        $model->shipping_from_fax = $req->get('shipping_from_fax');

        $model->is_dropship = parseBool($req->get('is_dropship'));
        $model->dropship_contact_id = $req->get('dropship_contact_id');

        return $model;
    }

    public function populateInvoice(SalesOrder $so,
                                    Invoice $model = null,
                                    $isCreate = true)
    {
        if (is_null($model))
            $model = Invoice::inst();

        if ($isCreate) {
            $model->organization_id = AuthToken::info()->organizationId;
            $model->invoice_number = $model->getNextInvoiceNumber();
            $model->invoice_status =
                ($so->sales_order_status == SalesOrder::AWAITING_PAYMENT)
                    ? Invoice::SENT
                    : Invoice::DRAFT;
        }

        $model->sales_order_id = $so->sales_order_id;
        $model->contact_id = $so->contact_id;

        $model->invoice_email = $so->invoice_email;
        $model->reference_number = $so->reference_number;
        $model->invoice_date = $so->invoice_date;
        $model->discount_contact_id = $so->discount_contact_id;
        $model->discount_amount_type = $so->discount_amount_type;
        $model->discount_amount_value = (float)$so->discount_amount_value;

        $model->adjustment_name = $so->adjustment_name;
        $model->adjustment_value = (float)$so->adjustment_value;
        $model->customer_notes = $so->customer_notes;
        $model->term_and_condition = $so->term_and_condition;
        $model->due_date = $so->due_date;

        $model->billing_address = $so->billing_address;
        $model->billing_region = $so->billing_region;
        $model->billing_district = $so->billing_district;
        $model->billing_province = $so->billing_province;
        $model->billing_country = $so->billing_country;
        $model->billing_zip = $so->billing_zip;
        $model->billing_fax = $so->billing_fax;
        $model->billing_phone = $so->billing_phone;
        $model->billing_mobile = $so->billing_mobile;

        $model->shipping_address = $so->shipping_address;
        $model->shipping_region = $so->shipping_region;
        $model->shipping_district = $so->shipping_district;
        $model->shipping_province = $so->shipping_province;
        $model->shipping_country = $so->shipping_country;
        $model->shipping_zip = $so->shipping_zip;
        $model->shipping_fax = $so->shipping_fax;
        $model->shipping_phone = $so->shipping_phone;
        $model->shipping_mobile = $so->shipping_mobile;

        $model->shipping_weight = (float)$so->shipping_weight;
        $model->shipping_weight_unit = $so->shipping_weight_unit ?? 'gr';
        $model->shipping_rate = (float)$so->shipping_rate;

        $model->shipping_carrier_id = $so->shipping_carrier_id;
        $model->shipping_carrier_code = $so->shipping_carrier_code;
        $model->shipping_carrier_name = $so->shipping_carrier_name;
        $model->shipping_carrier_service = $so->shipping_carrier_service;

//        $model->shipping_charge = (float)$so->shipping_charge;
        $model->tax_included = $so->tax_included;

        return $model;

    }

    private function adjustStock($details)
    {
        foreach ($details as $detail) {
            if (isset($detail->item) && $detail->item->track_inventory) {
                $adjust = new AdjustStockValue(
                    $detail->item_id,
                    (-1) * $detail->item_quantity
                );
                $this->stockService->adjust($adjust);
            }
        }
    }

    /**
     * @param array $request
     * @return BaseModel|MasterModel|SalesOrder
     * @throws Exception
     */
    public function storeData(array $request = [])
    {
        DB::beginTransaction();
        try {

            $isDraft = false;
            if (isset($request['is_draft']) && $request['is_draft'] == 1) {
                $isDraft = true;
            }

            $request['sales_order_status'] = $isDraft
                ? SalesOrder::DRAFT
                : SalesOrder::AWAITING_PAYMENT;

            $request['sales_order_number'] = $this->getNextSalesOrderNumber();

            //check availability shipping currier service and set it to custom courier
            if (!empty($request['shipping_carrier_service'])
                && !empty($request['shipping_rate'])) {
                $service = app(BaseServiceContract::class);
                $carrier = $service->getCarrierByCode('custom');
                $request['shipping_carrier_id'] = $carrier->id;
                $request['shipping_carrier_code'] = $carrier->code;
                $request['shipping_carrier_name'] = $carrier->name;
            }

            /*save sales order*/
            $so = $this->populate($request);
            if (!$so->save()) {
                DB::rollback();
                throw AppException::inst(
                    'Failed to create a new Sales Order.',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $so->errors
                );
            }

            /*save invoice*/
            $inv = $this->populateInvoice($so);
            if (!$inv->save()) {
                DB::rollBack();
                throw AppException::inst(
                    'Save invoice is failed.',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    $inv->errors);
            }

            $so->errors = [];
            $inv->errors = [];

            $sods = collect();

            if (!empty($request['details'])) {

                /*save sales order detail*/
                foreach ($request['details'] as $k => $v) {
                    if (isset($v['item_id'])) {

                        $item = Item::inst()
                            ->getByIdRef($v['item_id'])
                            ->firstOrFail();

                        $v['item_name'] = $item->item_name;
                        if (!isset($v['item_rate'])) {
                            //hanya di override ketika input kosong
                            $v['item_rate'] = $item->sales_rate;
                        }
                        $v['item_weight'] = $item->weight;
                        $v['item_weight_unit'] = $item->weight_unit;
                        $v['item_dimension_l'] = $item->dimension_l;
                        $v['item_dimension_w'] = $item->dimension_w;
                        $v['item_dimension_h'] = $item->dimension_h;
                        $v['track_inventory'] = $item->track_inventory;
                    }

                    $v['sales_order_id'] = $so->sales_order_id;

                    $data = SalesOrderDetail::inst()->populate($v);
                    if (!$data->save()) {
                        Log::error("so : $data->errors");
                    }

                    $sods->push($data);
                }

                /*validate sales order detail*/
                if (empty($sods) || !$sods->filter(function ($x) {
                        return isset($x->errors);
                    })->isEmpty()) {
                    DB::rollBack();
                    throw AppException::inst(
                        'Failed to create a new sales order detail',
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        $sods->map(function ($x) {
                            return $x->errors;
                        })
                    );
                }

                /* Update stock for inventory */
                if (AuthToken::isInventory()) {
                    //for create, is safe to assume that this function is only
                    //called when draft flag is false
                    if (!$isDraft) {
                        $this->adjustStock($sods);
                    }
                }

                $invDetails = $sods->map(function ($o) use ($inv) {
                    $detailInst = InvoiceDetail::inst();
                    $o['invoice_id'] = $inv->invoice_id;
                    $data = $detailInst->populate($o);
                    if (!$data->save()) {
                        Log::error("inv : $data->errors");
                        $data->errors = $data->errors->merge($inv->errors);
                    }

                    return $data;
                });


                if (empty($invDetails) || !$invDetails->filter(function ($x) {
                        return isset($x->errors);
                    })->isEmpty()) {
                    DB::rollBack();
                    throw AppException::inst(
                        'Failed to create a new invoice detail',
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        $invDetails->map(function ($x) {
                            return $x->errors;
                        })
                    );
                }

            }

            DB::commit();
            Log::info('Sales order committed.');

            //show related data
            $so->invoices = $inv->getBySalesOrderId($so->sales_order_id)->get();

            return $so;

        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            throw $e;
        }
    }

    public function updateData($id, array $request = [])
    {
        DB::beginTransaction();
        try {

            $so = $this->getByIdInOrgRef($id)->firstOrFail();

            if (empty($so)) {
                DB::rollback();
                throw AppException::inst(
                    'sales order not found.',
                    Response::HTTP_BAD_REQUEST);
            }

            $request['sales_order_status'] = $so->sales_order_status;

            //sales order hanya bisa di set draft ketika statusnya draft
            if (isset($request['is_draft']) && $request['is_draft'] == 1
                && $so->sales_order_status == SalesOrder::DRAFT) {
                $request['sales_order_status'] = SalesOrder::DRAFT;
            }

            $request['sales_order_number'] = $so->sales_order_number;

            //check availability shipping currier service and set it to custom courier
            if (!empty($request['shipping_carrier_service'])
                && !empty($request['shipping_rate'])) {
                $service = app(BaseServiceContract::class);
                $carrier = $service->getCarrierByCode('custom');
                $request['shipping_carrier_id'] = $carrier->id;
                $request['shipping_carrier_code'] = $carrier->code;
                $request['shipping_carrier_name'] = $carrier->name;
            }

            /*populate so*/
            $soPop = $this->populate($request, $so);
            if (!$soPop->save()) {
                DB::rollback();
                throw AppException::inst(
                    'update sales order is failed.',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    $soPop->errors);
            }

            /**
             * save invoice
             * asumsi hanya mengambil data pertama
             */
            $invInst = Invoice::inst();

            $invPop = $this
                ->populateInvoice($soPop,
                    $invInst
                        ->getBySalesOrderId($soPop->sales_order_id)
                        ->firstOrFail(),
                    false
                );

            if (!$invPop->save()) {
                DB::rollBack();
                throw AppException::inst(
                    'Update invoice failed.',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    $invPop->errors);
            }

            if (!empty($request['details'])) {

                /**
                 * remove all sales order details
                 * asumsi bahwa data sales order detail ketika update di kirim semua lagi dari frontend
                 */
                SalesOrderDetail::inst()
                    ->getBySalesOrderId($soPop->sales_order_id)
                    ->forceDelete();

                /*save new sales order detail*/
                $sods = array_map(function ($d) use ($so) {

                    $item = Item::inst()
                        ->getByIdRef($d['item_id'])
                        ->firstOrFail();

                    $d['item_name'] = $item->item_name;
                    if (!isset($d['item_rate'])) {
                        //hanya di override ketika input kosong
                        $d['item_rate'] = $item->sales_rate;
                    }
                    $d['item_weight'] = $item->weight;
                    $d['item_weight_unit'] = $item->weight_unit;
                    $d['item_dimension_l'] = $item->dimension_l;
                    $d['item_dimension_w'] = $item->dimension_w;
                    $d['item_dimension_h'] = $item->dimension_h;

                    $d['sales_order_id'] = $so->sales_order_id;

                    $data = SalesOrderDetail::inst()->populate($d);

                    if (!$data->save()) {
                        Log::error("Failed to save sales order: $data->errors");
                    }

                    return $data;
                }, $request['details']);

                /*validate sales order detail*/
                if (empty($sods) || !empty(array_column($sods, "errors"))) {
                    DB::rollBack();
                    throw AppException::inst(
                        'Some sales order detail has an error, no data will be saved.',
                        Response::HTTP_BAD_REQUEST,
                        $sods);
                }

                /**
                 * remove all invoice details
                 */
                InvoiceDetail::inst()->getByInvoiceIdRef($invPop->invoice_id)->forceDelete();

                /*save new invoice detail*/
                $invDetails = array_map(function ($d) use ($invPop) {
                    $detailInst = InvoiceDetail::inst();

                    $d['invoice_id'] = $invPop->invoice_id;
                    $data = $detailInst->populate($d);
                    if (!$data->save())
                        Log::error("save invoice failed. $data->errors");

                    return $data;
                }, $sods);

                /*validate invoice detail*/
                if (empty($invDetails) || !empty(array_column($invDetails, "errors"))) {
                    DB::rollBack();
                    throw AppException::inst(
                        'Some Invoice detail has an error. no data will be saved.',
                        Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            DB::commit();

            //TODO (jee) : ini kalo misal status invoice true
            //            if (!empty($so->invoice_email) && $sendingStatus) {
            //                $job = (new SendEmailJob(
            //                    $recipients = $so->invoice_email,
            //                    $cc = '',
            //                    $bcc = '',
            //                    $subject = '',
            //                    $message = Setting::findByKeyInOrg('web.template.invoice'),
            //                    $from = ''
            ////                    $attachments = $attachmentReq
            //                ))->onQueue(env('S3_QUEUE'))->delay(3);


            //                Log::info('Sending job Invoice email.');
            //                $this->dispatch($job);

            //                $invInst->sendPDFEmail($invInst);
            //            }

            //TODO (jee) : bagian ini harusnya menampilkan data invoice yang terkait (invoice list)
            $soPop->invoices = $invInst->getBySalesOrderId($soPop->sales_order_id)->get();

            return $soPop;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q;

        switch ($filterBy) {
            case self::DRAFT:
                $data = $data->where('sales_order_status', self::DRAFT);
                break;

            case self::CANCELED:
                $data = $data->where('sales_order_status', self::CANCELED);
                break;

            case self::AWAITING_PAYMENT:
                $data = $data->where('sales_order_status', self::AWAITING_PAYMENT);
                break;

            case self::AWAITING_SHIPMENT:
                $data = $data->where('sales_order_status', self::AWAITING_SHIPMENT);
                break;

            case self::FULFILLED:
                $data = $data->where('sales_order_status', self::FULFILLED);
                break;

            case Invoice::PAID:
                $data = $data->whereHas('invoices', function ($q) {
                    $q->where('invoice_status', Invoice::PAID);
                });
                break;

            case Invoice::PARTIALLY_PAID:
                $data = $data->whereHas('invoices', function ($q) {
                    $q->where('invoice_status',
                        Invoice::PARTIALLY_PAID);
                });
                break;

            case 'UNPAID':
                $data = $data->whereHas('invoices', function ($q) {
                    $q->where('invoices.invoice_status', Invoice::SENT);
                });
                break;

            case 'NOT_YET_SHIPPED':
                $data = $data->doesnthave('packages');
                break;

            case self::SHIPPED:
                $data = $data->whereHas('packages', function ($q) {
                    $q->whereHas('shipment');
                });
                break;

            case 'OVERDUE':
                $data = $data->whereHas('invoices', function ($q) {
                    $q->where('invoices.invoice_status', Invoice::SENT);
                })->where('due_date', '<', strtotime(date('Y-m-d')));
                break;
        }

        if (!empty($key)) {
            $data = $data
                ->where('sales_order_number', 'ILIKE', "%$key%")
                ->orWhereHas('contact', function ($query) use ($key) {
                    return $query->where('display_name', 'ILIKE', "%$key%")
                        ->orWhere('first_name', 'ILIKE', "%$key%")
                        ->orWhere('last_name', 'ILIKE', "%$key%")
                        ->orWhere('company_name', 'ILIKE', "%$key%")
                        ->orWhere('email', 'ILIKE', "%$key%");
                });
        }

        return $data->getInOrgRef()->nested();
    }

    //TODO(jee): realisasi pada has many invoice,
    // ini seharusnya dibikin loop untuk set status pada tiap invoice,
    // sedang untuk sementara dianggap 1 sales order 1 invoice
    public function setStatus($status, $id = null)
    {
        DB::beginTransaction();

        try {

            $so = $this;

            if (is_null($so) && !is_null($id))
                $so = $this
                    ->getByIdInOrgRef($id)
                    ->firstOrFail();

            $inv = Invoice::inst()
                ->getBySalesOrderId($so->sales_order_id)
                ->firstOrFail();

            //TODO (jee) : perlu ada kondisi tertentu
            switch ($status) {
                case self::DRAFT:
                    $so->sales_order_status = self::DRAFT;
                    $inv->invoice_status = Invoice::DRAFT;
                    break;

                case self::AWAITING_PAYMENT:
                    $so->sales_order_status = self::AWAITING_PAYMENT;
                    break;

                case self::AWAITING_SHIPMENT:
                    if ($so->sales_order_status === self::AWAITING_PAYMENT)
                        $so->sales_order_status = self::AWAITING_PAYMENT;

                    if ($so->sales_order_status === self::FULFILLED)
                        $so->sales_order_status = self::AWAITING_SHIPMENT;

                    break;

                case self::FULFILLED:
                    if ($inv->invoice_status === Invoice::PAID)
                        $so->sales_order_status = self::FULFILLED;
                    else
                        $so->sales_order_status = self::AWAITING_PAYMENT;
                    break;

                case self::SHIPPED:
                    $so->sales_order_status = self::SHIPPED;
                    // $inv->invoice_status = Invoice::SENT;
                    break;

                case self::CANCELED:
                    $so->sales_order_status = self::CANCELED;
                    $inv->invoice_status = Invoice::VOID;
                    break;
            }

            if (!$so->save()) {
                DB::rollback();
                throw AppException::inst(
                    "Can not set status Sales order.",
                    Response::HTTP_BAD_REQUEST, $so->errors);
            }

            if (!$inv->save()) {
                DB::rollback();
                throw AppException::inst(
                    "Can not set status invoice.",
                    Response::HTTP_BAD_REQUEST, $inv->errors);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @param array $ids
     * @return mixed
     * @throws Exception
     * @throws \Throwable
     */
    public function generateShipmentLabelBulkPDF(array $ids = [])
    {
        try {

            if (is_null($ids)) {
                throw AppException::inst('param ids not found', Response::HTTP_BAD_REQUEST);
            }

            $pop = [];

            foreach ($ids as $k => $id) {

                $so = $this->getByIdInOrgRef($id)
                    ->with(['sales_order_details', 'contact'])
                    ->first();

                if ($so) {

                    $area = RestService::inst()
                        ->getArea(
                            $so->shipping_country,
                            $so->shipping_province,
                            $so->shipping_district,
                            $so->shipping_region
                        );

                    $so->shipping_country_name
                        = !empty($area) && isset($area->country)
                        ? $area->country->name : '';
                    $so->shipping_province_name
                        = !empty($area) && isset($area->province)
                        ? $area->province->name : '';
                    $so->shipping_district_name
                        = !empty($area) && isset($area->district)
                        ? $area->district->name : '';
                    $so->shipping_region_name
                        = !empty($area) && isset($area->region)
                        ? $area->region->name : '';

                    $so->carrier = RestService::inst()->getCarrier($so->shipping_carrier_id);
                    $so->invoices = Invoice::inst()->getBySalesOrderId($so->sales_order_id)->get();

                    array_push($pop, [
                        'sales_order' => $so
                    ]);
                }
            }

            $pdf = App::make('snappy.pdf.wrapper');

            $organization = AuthToken::info();

            $area = RestService::inst()->getArea(
                $organization->countryId,
                $organization->provinceId,
                $organization->districtId,
                $organization->regionId
            );

            $organization->country_name =
                !empty($area) && isset($area->country)
                    ? $area->country->name : '';
            $organization->province_name =
                !empty($area) && isset($area->province)
                    ? $area->province->name : '';
            $organization->district_name =
                !empty($area) && isset($area->district)
                    ? $area->district->name : '';
            $organization->region_name =
                !empty($area) && isset($area->region)
                    ? $area->region->name : '';

            $pdf->loadView('shipment.so_shipment_label',
                ['data' => $pop, 'organization' => $organization]);

            $pdf->setPaper('a4')
                ->setOrientation('portrait')
                ->setOption('margin-bottom', 0);

            header('Content-Type: application/pdf');
            header('X-Frame-Options: NONE');

            return $pdf->inline();

        } catch (Exception $e) {
            throw $e;
        }
    }

    //FIXME MAYBE UNUSED CODE
    public function storeInvoice($soData = null, $sodData = null)
    {
        if (!empty($soData)) {
            $invObj = Invoice::inst();
            $invoiceData = $invObj
                ->getBySalesOrderId($soData->sales_order_id)
                ->first();

            if (!empty($invoiceData)) {
                $invoiceData->organization_id = AuthToken::info()->organizationId;
                $invoiceData->sales_order_id = $soData->sales_order_id;
                $invoiceData->contact_id = $soData->contact_id;
                $invoiceData->invoice_number = $invoiceData->getNextInvoiceNumber();
                $invoiceData->invoice_email = $soData->invoice_email;
                $invoiceData->reference_number = $soData->reference_number;
                $invoiceData->invoice_date = $soData->invoice_date;
//                $invoiceData->carrier_id = $soData->carrier_id; //existed

                $invoiceData->discount_contact_id = $soData->discount_contact_id;
                $invoiceData->discount_amount_type = $soData->discount_amount_type;
                $invoiceData->discount_amount_value = (float)$soData->discount_amount_value;

                $invoiceData->adjustment_name = $soData->adjustment_name;
                $invoiceData->adjustment_value = (float)$soData->adjustment_value;
                $invoiceData->customer_notes = $soData->customer_notes;
                $invoiceData->term_and_condition = $soData->term_and_condition;
                $invoiceData->due_date = $soData->due_date;
                $invoiceData->invoice_status = 'DRAFT';

                $invoiceData->billing_address = $soData->billing_address;
                $invoiceData->billing_region = $soData->billing_region;
                $invoiceData->billing_district = $soData->billing_district;
                $invoiceData->billing_province = $soData->billing_province;
                $invoiceData->billing_country = $soData->billing_country;
                $invoiceData->billing_zip = $soData->billing_zip;
                $invoiceData->billing_fax = $soData->billing_fax;
                $invoiceData->billing_phone = $soData->billing_phone;
                $invoiceData->billing_mobile = $soData->billing_mobile;

                $invoiceData->shipping_address = $soData->shipping_address;
                $invoiceData->shipping_region = $soData->shipping_region;
                $invoiceData->shipping_district = $soData->shipping_district;
                $invoiceData->shipping_province = $soData->shipping_province;
                $invoiceData->shipping_country = $soData->shipping_country;
                $invoiceData->shipping_zip = $soData->shipping_zip;
                $invoiceData->shipping_fax = $soData->shipping_fax;
                $invoiceData->shipping_phone = $soData->shipping_phone;
                $invoiceData->shipping_mobile = $soData->shipping_mobile;
//                $invoiceData->tax_included = $soData->tax_included;

                $invoiceData->shipping_weight = (float)$soData->shipping_weight;
                $invoiceData->shipping_weight_unit = $soData->shipping_weight_unit ?? 'gr';
                $invoiceData->shipping_rate = (float)$soData->shipping_rate;
                $invoiceData->shipping_carrier_id = $soData->shipping_carrier_id;
                $invoiceData->shipping_carrier_code = $soData->shipping_carrier_code;
                $invoiceData->shipping_carrier_name = $soData->shipping_carrier_name;
                $invoiceData->shipping_carrier_service = $soData->shipping_carrier_service;

                if (!$invoiceData->save()) {
                    return -1;
                }

                $invoiceDetailData = InvoiceDetail::where('invoice_id', $invoiceData->invoice_id)->truncate();
                if (!$invoiceDetailData) {
                    return -1;
                }

            } else {
                $invoiceData = Invoice::inst();
                $invoiceData->organization_id = AuthToken::info()->organizationId;
                $invoiceData->sales_order_id = $soData->sales_order_id;
                $invoiceData->contact_id = $soData->contact_id;
                $invoiceData->invoice_number = $invObj->getNextInvoiceNumber();
                $invoiceData->invoice_email = $soData->invoice_email;
                $invoiceData->reference_number = $soData->reference_number;
                $invoiceData->invoice_date = $soData->invoice_date;

                $invoiceData->discount_contact_id = $soData->discount_contact_id;
                $invoiceData->discount_amount_type = $soData->discount_amount_type;
                $invoiceData->discount_amount_value = (float)$soData->discount_amount_value;
                //                $invoiceData->shipping_charge = (float)$soData->shipping_charge;
                $invoiceData->adjustment_name = $soData->adjustment_name;
                $invoiceData->adjustment_value = (float)$soData->adjustment_value;
                $invoiceData->customer_notes = $soData->customer_notes;
                $invoiceData->term_and_condition = $soData->term_and_condition;
                $invoiceData->due_date = $soData->due_date;
                $invoiceData->invoice_status = 'DRAFT';

                $invoiceData->billing_address = $soData->billing_address;
                $invoiceData->billing_region = $soData->billing_region;
                $invoiceData->billing_district = $soData->billing_district;
                $invoiceData->billing_province = $soData->billing_province;
                $invoiceData->billing_country = $soData->billing_country;
                $invoiceData->billing_zip = $soData->billing_zip;
                $invoiceData->billing_fax = $soData->billing_fax;
                $invoiceData->billing_phone = $soData->billing_phone;
                $invoiceData->billing_mobile = $soData->billing_mobile;

                $invoiceData->shipping_address = $soData->shipping_address;
                $invoiceData->shipping_region = $soData->shipping_region;
                $invoiceData->shipping_district = $soData->shipping_district;
                $invoiceData->shipping_province = $soData->shipping_province;
                $invoiceData->shipping_country = $soData->shipping_country;
                $invoiceData->shipping_zip = $soData->shipping_zip;
                $invoiceData->shipping_fax = $soData->shipping_fax;
                $invoiceData->shipping_phone = $soData->shipping_phone;
                $invoiceData->shipping_mobile = $soData->shipping_mobile;
                //                $invoiceData->tax_included = $soData->tax_included;

                $invoiceData->shipping_weight = (float)$soData->shipping_weight;
                $invoiceData->shipping_weight_unit = $soData->shipping_weight_unit ?? 'gr';
                $invoiceData->shipping_rate = (float)$soData->shipping_rate;
                $invoiceData->shipping_carrier_id = $soData->shipping_carrier_id;
                $invoiceData->shipping_carrier_code = $soData->shipping_carrier_code;
                $invoiceData->shipping_carrier_name = $soData->shipping_carrier_name;
                $invoiceData->shipping_carrier_service = $soData->shipping_carrier_service;

                if (!$invoiceData->save()) {
                    return -1;
                }
            }

            $invoiceDetails = array_map(function ($d) use ($invoiceData) {
                $detailInst = InvoiceDetail::inst();
                $d['invoice_id'] = $invoiceData->invoice_id;
                $data = $detailInst->populate($d);
                $data->save();
                return $data;
            }, $sodData);

            if (empty($invoiceDetails) || !empty(array_column($invoiceDetails, "errors"))) {
                return -1;
            }
            return 1;
        }
        return -1;
    }

}
