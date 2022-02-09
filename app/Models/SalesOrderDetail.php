<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Support\Collection;

class SalesOrderDetail extends MasterModel
{

    protected $table = 'sales_order_details';

    protected $primaryKey = 'sales_order_detail_id';

    protected $columnDefault = array("*");

    protected $columnSimple = array("*");

    protected $appends = ['uom', 'amount', 'remaining_stock_qty'];

    protected $fillable = [
        'item_rate',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->nestedBelongConfigs = [
            "item" => [
                "item_id",
                "uom_id"
            ]
        ];
    }

    public function sales_order()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
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

    public function getUomAttribute()
    {
        $data = $this->item()->with('asset_uom')->select('item_id', 'uom_id')->first();
        $data->asset_uom = $data->asset_uom->makeHidden(array('uom_id', 'description', 'uom_status', 'organization_id'));

        return $data->asset_uom->name;
    }

    public function getItemRateAttribute($v)
    {
        //markup
        return round($v);
    }

    public function getAmountAttribute()
    {
        $priceBrut = $this->item_rate;
        if ($this->discount_amount_type == 'percentage')
            $disc = ((float)$this->discount_amount_value / 100) * $priceBrut;
        else if ($this->discount_amount_type == 'fixed')
            $disc = (float)$this->discount_amount_value;
        else
            $disc = 0;

        $priceNet = ($priceBrut - $disc);

        $amount = $priceNet * $this->item_quantity;

        return round($amount);
    }

    public function rules($id = null)
    {
        $orgId = $this->getOrganizationId();
        return [
            'sales_order_id' => 'required|integer|exists:sales_orders,sales_order_id',
            'item_id' => 'nullable|integer|exists:items,item_id|in_organization:items,' . $orgId . ',item_id',
            'item_name' => 'required|string|max:100',
            'item_rate' => 'required|numeric|between:0,9999999999',
            'item_quantity' => 'required|numeric|min:0',
            'discount_contact_id' => 'nullable|integer|exists:discount_contacts,discount_contact_id',
            'discount_amount_type' => 'nullable|required_with:discount_amount_value|string',
            'discount_amount_value' => 'nullable|numeric', //TODO(jee): saat ini tidak perlu depend on discount_contact_id
            'tax_id' => 'nullable|integer|exists:asset_taxes,tax_id',
            'tax_amount' => 'nullable|numeric|min:0', //TODO(jee): tidak depen on taxt id
            'item_weight' => 'sometimes|nullable|integer',
            'item_weight_unit' => 'sometimes|nullable|string',
            'item_dimension_l' => 'nullable|integer',
            'item_dimension_w' => 'nullable|integer',
            'item_dimension_h' => 'nullable|integer'
        ];
    }

    public static function inst($loginInfo = null)
    {
        return new self();
    }

    public function populate($request = [], BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);
        $model->sales_order_id = $req->get('sales_order_id');
        $model->item_id = (int)$req->get('item_id') ?? null;
        $model->item_name = $req->get('item_name');
        $model->item_rate = (float)$req->get('item_rate');
        $model->item_quantity = (float)$req->get('item_quantity');
        $model->discount_contact_id = intOrNull($req->get('discount_contact_id'));
        $model->discount_amount_type = $req->get('discount_amount_type');
        $model->discount_amount_value = (float)$req->get('discount_amount_value') ?? 0;
        $model->tax_id = intOrNull($req->get('tax_id'));
        $model->tax_amount = (float)$req->get('tax_amount') ?? 0;

        $model->item_weight = $req->get('item_weight') ?? 1;
        $model->item_weight_unit = $req->get('item_weight_unit') ?? 'gr';
        $model->item_dimension_l = $req->get('item_dimension_l') ?? null;
        $model->item_dimension_w = $req->get('item_dimension_w') ?? null;
        $model->item_dimension_h = $req->get('item_dimension_h') ?? null;

        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();
        return $data;
    }

    public function getByIdAndSalesOrderId($soId, $sodId)
    {
        return $this->where('sales_order_id', $soId)->where('sales_order_detail_id', $sodId)->first();
    }

    public function getBySalesOrderId($soId)
    {
        return $this->where('sales_order_id', $soId)->nested();
    }

    public function getRemainingStockQtyAttribute()
    {
        $item = $this->item()->getResults();
        return $item ? $item->stock_quantity : null;
    }
}
