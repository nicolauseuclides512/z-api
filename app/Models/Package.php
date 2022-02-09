<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Utils\DateTimeUtil;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Package extends MasterModel
{
    use SoftDeletes;

    protected $table = 'packages';

    protected $primaryKey = 'package_id';

    protected $columnDefault = ["*"];

    protected $columnSimple = ["*"];

    protected $appends = array("item_quantity");

    public function __construct()
    {
        parent::__construct();

        $this->nestedBelongConfigs = array(
            "sales_order" => array(
                "*"
            ),
            "shipment" => array('*')
        );

        $this->nestedHasManyConfigs = array(
            "package_details" => array('*')
        );
    }

    public function sales_order()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id')->nested();
    }

    public function package_details()
    {
        return $this->hasMany(PackageDetail::class, 'package_id')->nested();
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class, 'package_id');
    }

    public function getItemQuantityAttribute()
    {
        return $this->package_details()->count();
    }

    public function rules($id = null)
    {
        return array(
            'organization_id' => 'required|integer',
            'date' => 'numeric',
            'slip' => 'string|max:50',
            'internal_notes' => 'nullable|string',
            'sales_order_id' => 'integer|exists:sales_orders,sales_order_id',
            'package_status' => 'string'
        );
    }

    public static function boot()
    {

        static::saving(function (BaseModel $model) {
            #set validation
            $validation = Validator::make($model->attributes, $model->rules($model->package_id));

            #cheking validation
            if ($validation->fails()) {
                $model->errors = $validation->messages();
                return false;
            } else {
                return true;
            }
        });

        static::deleted(function ($model) {
            $model->package_details()->delete();
        });

        parent::boot();
    }

    public function getDateAttribute($v)
    {
        return DateTimeUtil::fromMicroSecond($v);
    }

    public function setDateAttribute($v)
    {
        $this->attributes['date'] = empty($v) ? null : DateTimeUtil::toMicroSecond($v);
    }

    public static function inst($loginInfo = null)
    {
        return new self();
    }

    public function populate($request = array(), BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);
        $model->organization_id = AuthToken::info()->organizationId;
        $model->date = $req->get('date');
        $model->slip = $req->get('slip');
        $model->internal_notes = $req->get('internal_notes');
        $model->sales_order_id = $req->get('sales_order_id');
        $model->package_status = $req->get('package_status');

        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();
        return $data;
    }

    public function scopeGetByIdAndSalesOrderId($q, $soId, $id)
    {
        return $q->getInOrgRef()->where('sales_order_id', $soId)->where('package_id', $id)->nested();
    }

    public function scopeGetBySalesOrderId($q, $soId)
    {
        return $q->getInOrgRef()->where('sales_order_id', $soId);
    }

    public function storeExec(array $request = [], $model = null)
    {
        if (!empty($request)) {
            $data = $this->populate($request, $model);
            if ($data->save()) {
                $pd = $this->storeDetail($request['package_details'], $data->package_id);

                if (in_array(-1, $pd)) {
                    Log::error('error when save detail package');
                    return null;
                }

                return $data;
            }
            Log::error('error when save package ' . json_encode($data->errors));

            return null;
        }
        Log::error('empty request package');
        return null;
    }

    /*asumsi package harus memiliki detail*/
    public function storeDetail($details = [], $packageId = null)
    {
        if (!empty($details)) {

            PackageDetail::inst()->where('package_id', $packageId)->forceDelete();

            return array_map(function ($d) use ($packageId) {
                $pd = PackageDetail::inst();
                $pd->sales_order_detail_id = $d['sales_order_detail_id'];
                $pd->quantity = $d['item_quantity'];
                $pd->package_id = $packageId;
                if (!$pd->save()) {
                    Log::error('error when save detail package ' . json_encode($pd->errors));
                    return -1;
                }
                return 1;
            }, $details->toArray());
        }

        Log::error('empty detail data request');
        return -1;
    }
}
