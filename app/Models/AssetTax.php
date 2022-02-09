<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Models\Base\RestfulModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Validator;

class AssetTax extends MasterModel
{

    protected $table = 'asset_taxes';

    protected $primaryKey = 'tax_id';

    protected $columnStatus = 'tax_status';

    protected $columnDefault = array("tax_id", "name", "percent");

    protected $columnSimple = array("tax_id", "name", "percent");

    protected $showActiveOnly = true;

    public function items()
    {
        return $this->hasMany(Item::class, 'tax_id');
    }

    public function salesOrderDetails()
    {
        return $this->hasMany(SalesOrderDetail::class, 'tax_id');
    }

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'tax_id');
    }

    public function rules($id = null)
    {
        return [
            'organization_id' => 'required|integer',
            'name' => 'required|string|max:100',
            'percent' => 'required|integer|between:1,100',
            'tax_status' => 'required|integer|in:0,1'
        ];
    }

    public static function inst()
    {
        return new AssetTax();
    }

    public function populate($request = array(), BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);

        $model->organization_id = (int)AuthToken::info()->organizationId;
        $model->name = $req->get('name');
        $model->percent = (int)$req->get('percent');
        $model->tax_status = (int)$req->get('tax_status');
        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();

        switch ($filterBy) {
            case self::STATUS_ACTIVE :
                $data->where($this->columnStatus, "=", self::STATUS_ACTIVE);
                break;
            case self::STATUS_INACTIVE :
                $data->where($this->columnStatus, "=", self::STATUS_INACTIVE);
                break;
        }

        return $data;
    }

    public function scopeGetByNameInOrg($q, $name)
    {
        return $q
            ->getInOrgRef()
            ->where('name', 'ilike', $name)
            ->firstOrFail();
    }
}