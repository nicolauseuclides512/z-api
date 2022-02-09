<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Support\Collection;

//TODO (*) : kemungkinan tidak akan menggunakan fasilitas soft delete karena terdapat fungsi replace dengan deleted
class InvoiceDetail extends MasterModel
{
    protected $table = 'invoice_details';

    protected $primaryKey = 'invoice_detail_id';

    protected $columnDefault = ['*'];

    protected $columnSimple = ['*'];

    protected $appends = ['uom', 'amount', 'remaining_stock_qty'];

    public function __construct()
    {
        parent::__construct();

        $this->nestedBelongConfigs = [
            "item" => ["*"]
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function asset_tax()
    {
        return $this->belongsTo(AssetTax::class, 'tax_id');
    }

    public function discount_contact()
    {
        return $this->hasOne(DiscountContact::class, 'discount_contact_id');
    }

    public function rules($id = null)
    {
        return [
            'invoice_id' => 'integer|exists:invoices,invoice_id',
            'item_id' => 'nullable|integer|exists:items,item_id',
            'item_name' => 'required|string|max:100',
            'item_rate' => 'required|numeric|between:0,999999999',
            'item_quantity' => 'required|numeric|min:0',
            'discount_contact_id' => 'nullable|integer|exists:discount_contacts,discount_contact_id',
            'discount_amount_type' => 'nullable|required_with:discount_amount_value|string',
            'discount_amount_value' => 'nullable|numeric', //TODO(jee): saat ini tidak perlu depend on discount_contact_id
            'tax_id' => 'nullable|integer|exists:asset_taxes,tax_id',
            'tax_amount' => 'nullable|numeric|between:0,9999999999', //TODO(jee): tidak depen on taxt id
            'item_weight' => 'sometimes|nullable|integer',
            'item_weight_unit' => 'sometimes|nullable|string',
            'item_dimension_l' => 'nullable|integer',
            'item_dimension_w' => 'nullable|integer',
            'item_dimension_h' => 'nullable|integer'
        ];
    }

//
//    protected static function boot()
//    {
//        parent::boot();
//
////        self::defaultObserver();
//
//        // You can also replace this with static::creating or static::updating
//        // if you want to call specific validation functions for each case.
//        static::saving(function ($model) {
//            #set validation
//            $validation = Validator::make($model->attributes, static::$rules);
//
//            #cheking validation
//            if ($validation->fails()) {
//                $model->errors = $validation->messages();
//                return false;
//            } else {
//                return true;
//            }
//        });
//    }

    public function getUomAttribute()
    {
        $data = $this->item()->with('asset_uom')->select('item_id', 'uom_id')->where('organization_id', AuthToken::info()->organizationId)->first();
//        $data->asset_uom = $data->asset_uom->makeHidden(array('uom_id', 'description', 'uom_status', 'organization_id'));
        return $data ? $data->asset_uom->name : "";
    }

    public function getItemRateAttribute($v)
    {
        //markup
        return round($v);
    }

    public function getAmountAttribute()
    {
//        $brut = parseToGram($this->item_rate, $this->item_weight_unit);

        $priceBrut = $this->item_rate;
        if ($this->discount_amount_type == 'percentage')
            $disc = ((float)$this->discount_amount_value / 100) * $priceBrut;
        else if ($this->discount_amount_type == 'fixed')
            $disc = (float)$this->discount_amount_value;
        else
            $disc = 0;

        $priceNet = ($priceBrut - $disc);

//        if ($taxStatus) {
//            $taxed = 0.1 * $net; #tax 10%
//            return $net + $taxed;
//        }

        $amount = $priceNet * $this->item_quantity;

        return round($amount);
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

        $model->invoice_id = $req->get('invoice_id');
        $model->item_id = $req->get('item_id');
        $model->item_name = $req->get('item_name');
        $model->item_rate = (float)$req->get('item_rate');
        $model->item_quantity = (float)$req->get('item_quantity');

        $model->discount_contact_id = $req->get('discount_contact_id');
        $model->discount_amount_type = $req->get('discount_amount_type');
        $model->discount_amount_value = (float)$req->get('discount_amount_value');

        $model->tax_id = $req->get('tax_id');
        $model->tax_amount = (float)$req->get('tax_amount');

        $model->item_weight = $req->get('item_weight');
        $model->item_weight_unit = $req->get('item_weight_unit');
        $model->item_dimension_l = $req->get('item_dimension_l');
        $model->item_dimension_w = $req->get('item_dimension_w');
        $model->item_dimension_h = $req->get('item_dimension_h');


        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();
        return $data;
    }

    public function getByInvoiceId($invoiceId)
    {
        return $this->where('invoice_id', $invoiceId)->get();
    }

    public function getByIdAndInvoiceId($invId, $invDId)
    {
        return $this->where('invoice_id', $invId)->where('invoice_detail_id', $invDId)->first();
    }

    public function getByInvoiceIdRef($invoiceId)
    {
        return $this->where('invoice_id', $invoiceId);
    }

    public function getByIdAndInvoiceIdRef($invId, $invDId)
    {
        return $this->where('invoice_id', $invId)->where('invoice_detail_id', $invDId);
    }

    public function getRemainingStockQtyAttribute()
    {
        return $this->item()->getResults()->stock_quantity;
    }
}
