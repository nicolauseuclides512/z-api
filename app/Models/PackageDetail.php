<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Support\Collection;

class PackageDetail extends MasterModel
{
    protected $table = 'package_details';

    protected $primaryKey = 'package_detail_id';

    protected $columnDefault = array("*");

    protected $columnSimple = array("*");


    public function __construct()
    {
        parent::__construct();

        $this->nestedBelongConfigs = array(
            "salesOrderDetail" => array("*"),
            "package" => array("*")
        );

        $this->nestedHasManyConfigs = [];
    }

    public function salesOrderDetail()
    {
        return $this->belongsTo(SalesOrderDetail::class, 'sales_order_detail_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * The tabel relation BELONG_TO_MANY
     * @param null $id
     * @return array
     */

    public function rules($id = null)
    {
        return [
            'sales_order_detail_id' => 'required|integer|exists:sales_order_details,sales_order_detail_id',
            'quantity' => 'required|integer',
            'package_id' => 'required|integer|exists:packages,package_id'

        ];

    }

//    protected static function boot()
//    {
//        parent::boot();

//        #custom validation
//        Validator::extend('greater_than', function ($attribute, $value, $parameters, $validator) {
//            $min_field = $parameters[0];
//            $data = $validator->getData();
//            $min_value = $data[$min_field];
//            return $value > $min_value;
//        });
//
//        Validator::replacer('greater_than', function ($message, $attribute, $rule, $parameters) {
//            return str_replace(':field', $parameters[0], 'The selected ' . $attribute . ' must greater than ' . $parameters[0]);
//        });

    // You can also replace this with static::creating or static::updating
    // if you want to call specific validation functions for each case.
//        static::saving(function ($model) {
//            #set validation
//            $validation = Validator::make($model->attributes, static::rules($model->package_detail_id));
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

    public static function inst()
    {
        return new PackageDetail();
    }

    public function populate($request = array(), BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);
        $model->sales_order_detail_id = $req->get('sales_order_detail_id');
        $model->quantity = $req->get('quantity');
        $model->package_id = $req->get('package_id');
        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();
        return $data;
    }

    public function getByPackageId($packageId)
    {
        return $this->where('package_id', $packageId);
    }
}
