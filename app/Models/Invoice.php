<?php

namespace App\Models;

use App\Domain\Contracts\DocumentCounterContract;
use App\Domain\Contracts\StockContract;
use App\Domain\Data\DocumentCounterSetupData;
use App\Domain\ValueObjects\AdjustStockValue;
use App\Exceptions\AppException;
use App\Jobs\SendEmailJob;
use App\Models\Base\BaseModel;
use App\Services\Gateway\Rest\RestService;
use App\Utils\DateTimeUtil;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class Invoice extends MasterModel
{
    const URI = 'com.zuragan.invoice';
    const NUMBERING_PREFIX = 'INV';

    const DRAFT = 'DRAFT';

    const SENT = 'SENT';

    const PAID = 'PAID';

    const PARTIALLY_PAID = 'PARTIALLY_PAID';

    const VOID = 'VOID';

    protected $table = 'invoices';

    protected $primaryKey = 'invoice_id';

    protected $columnDefault = ["*"];

    protected $columnSimple = ["*"];

    protected $appends = [
        "total",
        'sub_total',
        "tax",
        'shipping_charge',
        'discount',
        'balance_due'
    ];

    private $stockService;
    private $counterService;

    public function __construct()
    {
        parent::__construct();

        $this->nestedBelongConfigs = [
            "contact" => ["*"],
            "sales_order" => ["sales_order_id", "sales_order_number", 'due_date']
        ];

        $this->nestedHasManyConfigs = [
            "invoice_details" => ["*"]
        ];

        $this->stockService = app(StockContract::class);
        $this->counterService = app(DocumentCounterContract::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function invoice_details()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function sales_order()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function discount_contact()
    {
        return $this->hasOne(DiscountContact::class, 'discount_contact_id');
    }

    /*end relation*/
    public function getInvoiceDateAttribute($v)
    {
        return DateTimeUtil::fromMicroSecond($v);
    }

    public function setInvoiceDateAttribute($v)
    {
        $this->attributes['invoice_date'] = empty($v) ? null : DateTimeUtil::toMicroSecond($v);
    }

    public function getDueDateAttribute($v)
    {
        return DateTimeUtil::fromMicroSecond($v);
    }

    public function setDueDateAttribute($v)
    {
        $this->attributes['due_date'] = empty($v) ? null : DateTimeUtil::toMicroSecond($v);
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

    public function getNextInvoiceNumber($commit = true)
    {
        $this->setupNumbering();
        $nextId = $this->counterService->getNumbering(self::URI, $commit);
        return $nextId;
    }

    /**
     * The tabel relation BELONG_TO_MANY
     * @param null $id
     * @return array
     */
    public function rules($id = null)
    {
        $forUpdate = $id ? ',' . $id . ',invoice_id' : '';

        return [
            'organization_id' => 'required|integer',
//        'user_id' => 'required|integer|exists:users,user_id',
            'sales_order_id' => 'integer|exists:sales_orders,sales_order_id',
//        'salesperson_id' => 'integer|exists:asset_sales_persons,salesperson_id',
            'contact_id' => 'required|integer|exists:contacts,contact_id',
            'invoice_number' => 'required|string|max:50|org_unique:invoices,invoice_number' . $forUpdate,
            'reference_number' => 'nullable|string|max:100',
            'invoice_date' => 'integer',
//        'payment_term_id' => 'integer|exists:asset_payment_terms,payment_term_id',
            'discount_contact_id' => 'nullable|integer|exists:discount_contacts,discount_contact_id',
            'discount_amount_type' => 'nullable|required_with:discount_amount_value|string',
            'discount_amount_value' => 'nullable|numeric', //TODO(jee): saat ini tidak perlu depend on discount_contact_id

//            'shipping_charge' => 'numeric|min:0',
            'adjustment_name' => 'nullable|string|max:100',
            'adjustment_value' => 'numeric',
//            'total' => 'required|numeric|min:0',
            'customer_notes' => 'nullable|string|max:500',
            'term_and_condition' => 'nullable|string',
            'due_date' => 'nullable|numeric',
            'invoice_status' => 'required|string',
            'billing_address' => 'nullable|string|max:255',
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
            'shipping_phone' => 'nullable|string|min:9|max:15|regex:/^\+?[^a-zA-Z]{5,}$/',
            'shipping_mobile' => 'nullable|string|min:9|max:15|regex:/^\+?[^a-zA-Z]{5,}$/',
//            'tax_included' => 'boolean'
            'shipping_weight' => 'numeric|min:0',
            'shipping_weight_unit' => 'required_with:shipping_weight|string|in:gr,kg',
            'shipping_rate' => 'numeric|between:0,9999999999',
            'shipping_carrier_id' => 'nullable|integer',
            'shipping_carrier_name' => 'nullable|string',
            'shipping_carrier_code' => 'nullable|string',
            'shipping_carrier_service' => 'nullable|string',
            'invoice_email' => 'nullable|string'
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            #set validation
            $validation = Validator::make($model->attributes, static::rules($model->invoice_id));

            #cheking validation
            if ($validation->fails()) {
                $model->errors = $validation->messages();
                return false;
            } else {
                return true;
            }
        });

        //delete inner.. akan menyisakan data karna menggunakan soft delete
        parent::deleting(function ($inv) {
            foreach (['invoice_details'] as $relation) {
                foreach ($inv->{$relation} as $item) {
                    $item->delete();
                }
            }
        });
    }

    public function getShippingChargeAttribute()
    {
//        $shippingCharge = $this->shipping_rate * $this->shipping_weight;
        $shippingCharge = (float)$this->shipping_rate;

        //markup
        return round($shippingCharge);
    }

    public function getDiscountAttribute()
    {

        if ($this->discount_amount_type == 'percentage')
            $disc = ((float)$this->discount_amount_value / 100) * $this->subTotal;
        else if ($this->discount_amount_type == 'fixed')
            $disc = (float)$this->discount_amount_value;
        else
            $disc = 0;
        return $disc;
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
        if (!empty($this->invoice_details)) {
            foreach ($this->invoice_details as $k => $v) {
                $subTotal += $v->amount;
            }
        }

        //markup
        return round($subTotal);
    }

    public function getTaxAttribute()
    {
        return !$this->tax_included
            ? $this->sub_total * 0.1 : 0;
    }

    public function getBalanceDueAttribute()
    {
        $payments = $this->payments()->get();

        if (!$payments->isEmpty()) {
            $totalPayment = $payments->map(function ($o) {
                return $o->amount;
            })->sum();

            return (float)$this->total - $totalPayment;
        }

        //markup
        return round($this->total);

    }

    public static function inst()
    {
        return new self();
    }

    public function populate($request = [], BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);
        $model->organization_id = intOrNull(AuthToken::info()->organizationId);
        $model->sales_order_id = $req->get('sales_order_id');
        $model->contact_id = intOrNull($req->get('contact_id'));
        $model->invoice_number = $req->get('invoice_number');
        $model->invoice_email = $req->get('invoice_email');
        $model->reference_number = $req->get('reference_number');
        $model->invoice_date = $req->get('invoice_date');

        $model->discount_contact_id = intOrNull($req->get('discount_contact_id'));
        $model->discount_amount_type = $req->get('discount_amount_type');
        $model->discount_amount_value = (float)$req->get('discount_amount_value');
        $model->shipping_weight = (float)$req->get('shipping_weight');
        $model->shipping_weight_unit = $req->get('shipping_weight_unit') ?? 'gr';
        $model->shipping_rate = (float)$req->get('shipping_rate');
//        $model->shipping_charge = (float)$req->get('shipping_charge');
        $model->adjustment_name = $req->get('adjustment_name');
        $model->adjustment_value = (float)$req->get('adjustment_value');
//        $model->total = (float)$req->get('total');
        $model->customer_notes = $req->get('customer_notes');
        $model->term_and_condition = $req->get('term_and_condition');
        $model->due_date = $req->get('due_date');
        $model->invoice_status = $req->get('invoice_status');

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
        $model->shipping_phone = $req->get('shipping_phone');
        $model->shipping_mobile = $req->get('shipping_mobile');
        $model->shipping_zip = $req->get('shipping_zip');
        $model->shipping_fax = $req->get('shipping_fax');
        $model->tax_included = $req->get('tax_included');

        $model->shipping_carrier_id = intOrNull($req->get('shipping_carrier_id'));
        $model->shipping_carrier_code = $req->get('shipping_carrier_code');
        $model->shipping_carrier_name = $req->get('shipping_carrier_name');
        $model->shipping_carrier_service = $req->get('shipping_carrier_service');

        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();
        return $data;
    }

    public function getBySalesOrderId($soId)
    {
        return $this
            ->where('organization_id', AuthToken::info()->organizationId)
            ->where('sales_order_id', $soId);
    }

    public function getByIdAndSalesOrderId($soId, $id)
    {
        return $this
            ->where('organization_id', AuthToken::info()->organizationId)
            ->where('sales_order_id', $soId)
            ->where('invoice_id', $id);
    }

    private function adjustStock($so, $soLastStatus)
    {
        $operand = $this->getUpdateStockOperand(
            $soLastStatus,
            $so->sales_order_status
        );

        foreach ($so->sales_order_details as $detail) {
            if ($detail->item->track_inventory) {
                $adjust = new AdjustStockValue(
                    $detail->item_id,
                    $operand * $detail->item_quantity
                );
                $this->stockService->adjust($adjust);
            }
        }
    }

    private function getUpdateStockOperand($prevStatus, $newStatus)
    {
        if ($prevStatus == SalesOrder::DRAFT) {
            if ($newStatus == SalesOrder::AWAITING_PAYMENT ||
                $newStatus == SalesOrder::AWAITING_SHIPMENT) {
                return -1;
            }
        }

        if ($prevStatus == SalesOrder::AWAITING_PAYMENT ||
            $prevStatus == SalesOrder::AWAITING_SHIPMENT) {
            if ($newStatus == SalesOrder::CANCELED ||
                $newStatus == SalesOrder::DRAFT) {
                return 1;
            }
        }

        // if no status change, then don't adjust stock
        return 0;
    }

    /**
     * @param $id
     * @param $status
     * @return bool
     * @throws Exception
     */
    public function setStatus($id, $status)
    {
        DB::beginTransaction();
        try {

            $inv = self::getByIdInOrgRef($id)
                ->firstOrFail();

            $so = SalesOrder::inst()
                ->getByIdInOrgRef($inv->sales_order_id)
                ->firstOrFail();

            $soLastStatus = $so->sales_order_status;
            if ($soLastStatus === SalesOrder::CANCELED) {
                return false;
            }

            switch ($status) {
                case self::DRAFT:
                    $inv->invoice_status = self::DRAFT;
                    $so->sales_order_status = SalesOrder::DRAFT;
                    break;

                case self::SENT:
                    $inv->invoice_status = self::SENT;
                    $so->sales_order_status = SalesOrder::AWAITING_PAYMENT;
                    break;

                case self::PARTIALLY_PAID:
                    $inv->invoice_status = self::PARTIALLY_PAID;
                    $so->sales_order_status = SalesOrder::AWAITING_PAYMENT;
                    break;

                case self::PAID:
                    $inv->invoice_status = self::PAID;
                    //notes shipment_status on sales order
                    if ($so->shipment_status === 'SHIPPED')
                        $so->sales_order_status = SalesOrder::FULFILLED;
                    else
                        $so->sales_order_status = SalesOrder::AWAITING_SHIPMENT;
                    break;

                case self::VOID:
                    $inv->invoice_status = self::VOID;
                    $so->sales_order_status = SalesOrder::CANCELED;
                    break;
            }

            if (!$inv->save()) {
                DB::rollback();
                Log::error('error save update invoice status');
                throw AppException::inst(
                    'can not set status invoice.',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    $inv->errors);
            }

            if (!$so->save()) {
                DB::rollback();
                Log::error('error update sales order status');
                throw AppException::inst(
                    'can not set status sales order.',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    $so->errors);
            }

            if (AuthToken::isInventory()) {
                $this->adjustStock($so, $soLastStatus);
            }

            DB::commit();

            return true;

        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $invoice
     * @param null $req
     * @return bool
     * @throws Exception
     * @throws \Throwable
     */
    public function sendPDFEmail($invoice, $req = null)
    {
        try {
            $newInvoice = $this->_popInvoice($invoice);

            $pdf = App::make('snappy.pdf.wrapper');

            $pdf->loadView('invoice.invoice_pdf', ['invoice' => $newInvoice])
                ->setPaper('a4')
                ->setOrientation('portrait')
                ->setOption('margin-bottom', 0);

            $pdfName = "INVOICE-" . $newInvoice->invoice_number . ".pdf";

            $tmpFName = tempnam("/tmp", "INVOICE_") . ".pdf";
            file_put_contents($tmpFName, $pdf->output());

            $attachmentReq = [];

            array_push($attachmentReq,
                [
                    'name' => $pdfName,
                    'url' => $tmpFName
                ]
            );

            $template = json_decode(Setting::findByKeyInOrg('web.template.notification.email.invoice')['value']);

            $templates = <<<EOT
Yth. %salutation% %name%

Salam sejahtera,
Terima kasih atas order anda melalui %portal name%.zuragan.com
Nomor invoice anda adalah %invoice number%.
Berikut kami sertakan invoice order sesuai dengan order yang anda lakukan pada %date order%.

Setelah melakukan pembayaran mohon segera melakukan KONFIRMASI PEMBAYARAN agar pesanan Anda dapat segera kami proses.
Silakan menghubungi kami kapan saja untuk mendapatkan informasi mengenai order Anda.

Salam hangat,

zuragan.com

Abaikan surat elektronik ini jika anda merasa tidak mendaftarkan email ini pada zuragan.
EOT;
            $newReq = [
                'recipients' => $newInvoice->invoice_email,
                'cc' => '',
                'bcc' => '',
                'subject' => 'invoice ' . $newInvoice->invoice_number,
                'message' => $template->id,
                'from' => '',
                'attachments' => $attachmentReq
            ];

            if (!empty($req)) {
                $newReq = [
                    'recipients' => $req['recipients'],
                    'cc' => $req['cc'],
                    'bcc' => $req['bcc'],
                    'subject' => $req['subject'],
                    'message' => $req['message'],
                    'from' => $req['from'],
                    'attachments' => $attachmentReq
                ];
            }

            $job = (new SendEmailJob(
                $recipients = $newReq['recipients'],
                $cc = $newReq['cc'],
                $bcc = $newReq['bcc'],
                $subject = $newReq['subject'],
                $message = $newReq['message'],
                $from = $newReq['from'],
                $attachments = $newReq['attachments']

            ))->delay(Carbon::now()->addSecond(5));

            $this->dispatch($job);

            if ($newInvoice->invoice_status == Invoice::DRAFT)
                SalesOrder::inst()
                    ->setStatus(
                        $newInvoice->sales_order_id,
                        SalesOrder::AWAITING_PAYMENT
                    );

            Log::info('Send email and attach invoice successfully.');
            return true;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $invoice
     * @param bool $getFileUrl
     * @return mixed
     * @throws Exception
     * @throws \Throwable
     */
    public function generatePDF($invoice, $getFileUrl = false)
    {
        $filePath = 'temp/' . strtoupper($invoice->invoice_number) . '.pdf';
        $disk = Storage::disk('s3');

        if ($getFileUrl && $disk->has($filePath)) {
            return $disk->url($filePath);
        }

        $pdf = App::make('snappy.pdf.wrapper');
        $data = $this->_popInvoice($invoice);
        $pdf->loadView('invoice.invoice_pdf', ['invoice' => $data])
            ->setPaper('a4')
            ->setOrientation('portrait')
            ->setOption('margin-top', 1)
            ->setOption('margin-bottom', 1)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0);

        if ($getFileUrl) {
            $disk->put($filePath, $pdf->output());
            return $disk->url($filePath);
        }

        return $pdf->inline();
    }

    /**
     * @param $status
     * @return string
     * @throws AppException
     */
    public function _getStatusAlias($status)
    {
        switch ($status) {
            case self::DRAFT:
                return 'D';
                break;
            case self::SENT:
                return 'S';
                break;
            case self::PAID:
                return 'P';
                break;
            case self::PARTIALLY_PAID:
                return 'PA';
                break;
            case self::VOID:
                return 'V';
                break;
            default:
                throw AppException::flash(
                    Response::HTTP_NOT_FOUND,
                    "Status doesn't exist."
                );
        }
    }

    public function generateBulkPDF(array $soIds = [],
                                    $getFileUrl = false)
    {
        $tempInvFileName = [];

        $collectSoId = collect($soIds);

        $invoices = $collectSoId->map(
            function ($id) use (&$tempInvFileName) {
                $existingInvoice = $this->getBySalesOrderId($id)->first();
                if ($existingInvoice) {
                    array_push($tempInvFileName,
                        md5($existingInvoice->invoice_id . $this->_getStatusAlias($existingInvoice->invoice_status))
                    );
                    return $this->_popInvoice($existingInvoice);
                }
            });

        //name = date_status.pdf => 12052019_DV.pdf
        $filePath = 'temp/' .
            Carbon::now()->format('dmY') .
            '_' .
            implode("", $tempInvFileName) .
            '.pdf';

        $disk = Storage::disk('s3');

        if ($getFileUrl && $disk->has($filePath)) {
            return $disk->url($filePath);
        }

        if ($invoices) {
            $pdf = App::make('snappy.pdf.wrapper');
            $pdf->loadView('invoice.invoice_bulk_pdf', ['invoices' => $invoices])
                ->setPaper('a4')
                ->setOrientation('portrait')
                ->setOption('margin-top', 1)
                ->setOption('margin-bottom', 1)
                ->setOption('margin-left', 0)
                ->setOption('margin-right', 0);

            if ($getFileUrl) {
                Log::debug('url response');

                //save to s3
                $disk->put($filePath, $pdf->output());
                $url = $disk->url($filePath);

                return $url;
            }

            Log::debug('inline response');
            return $pdf->inline();
        }

    }

    /**
     * @param $invoice
     * @return bool
     * @throws Exception
     * @throws \Throwable
     */
    public function _popInvoice($invoice)
    {
        try {
            if (empty($invoice)) {
                Log::error('Send email and attach invoice failed. Error parameters');
                return false;
            }

            $info = AuthToken::info();

            $cli = new RestService(
                new Client([
                    'timeout' => Config::get('gateway.timeout'),
                    'connect_timeout' =>
                        Config::get('gateway.connect_timeout',
                            Config::get('gateway.timeout')
                        )
                ])
            );

            $cli->setBaseUri(env('GATEWAY_ASSET_API'));

            $promise = [
                'orgArea' => $cli->getAsync('/countries/areas',
                    [
                        'countryId' => $info->countryId,
                        'provinceId' => $info->provinceId,
                        'districtId' => $info->districtId,
                        'regionId' => $info->regionId,
                    ]
                ),
                'area' => $cli->getAsync('/countries/areas',
                    [
                        'countryId' => $invoice->billing_country,
                        'provinceId' => $invoice->billing_province,
                        'districtId' => $invoice->billing_district,
                        'regionId' => $invoice->billing_region,
                    ]
                ),
                'weightUnit' => $cli->getAsync('/weight_units/code/' . $invoice->shipping_weight_unit,
                    [
                        'code' => $invoice->shipping_weight_unit
                    ]
                )
            ];

            $res = Promise\unwrap($promise);

            $orgArea = json_decode($res['orgArea']->getBody())->data ?? [];

            $invoice->organization = [
                'address' => $info->address,
                'country' => $orgArea->country ? $orgArea->country->name : '',
                'province' => $orgArea->province ? $orgArea->province->name : '',
                'district' => $orgArea->district ? $orgArea->district->name : '',
                'region' => $orgArea->region ? $orgArea->region->name : '',
                'zip' => $info->zip,
                'phone' => $info->phone
            ];

            $area = json_decode($res['area']->getBody())->data ?? [];

            $invoice->billing_area = [
                'country_name' => $area->country ? $area->country->name : '',
                'province_name' => $area->province ? $area->province->name : '',
                'district_name' => $area->district ? $area->district->name : '',
                'region_name' => $area->region ? $area->region->name : ''
            ];

            $weightUnit = json_decode($res['weightUnit']->getBody())->data ?? [];

            $invoice['shipping_weight_unit_name'] = $weightUnit->data->name ?? '';

            return $invoice;

        } catch (Exception $e) {
            throw $e;
        }
    }
}
