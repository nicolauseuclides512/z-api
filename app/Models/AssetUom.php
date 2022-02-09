<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Support\Collection;

class AssetUom extends MasterModel
{
    protected $table = 'asset_uoms';

    protected $primaryKey = 'uom_id';

    protected $columnStatus = 'uom_status';

    protected $columnDefault = array("uom_id", "name", "description");

    protected $columnSimple = array("uom_id", "name");

    protected $showActiveOnly = true;

    public function rules($id = null)
    {
        return [
            'organization_id' => 'required|integer',
            'name' => 'required|string|max:50',
            'description' => 'string|max:255',
            'uom_status' => 'required|integer|in:0,1',
            'is_default' => 'boolean'
        ];
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

        $model->organization_id = (int)AuthToken::info()->organizationId;
        $model->name = $req->get('name');
        $model->description = $req->get('description');
        $model->uom_status = (int)$req->get('uom_status');
        $model->is_default = $req->get('is_default') ?? false;

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

        if (!empty($key)) {
            $data = $data
                ->where("name", "ILIKE", "%" . $key . "%");
        }


        return $data;
    }

    public function scopeGetByNameInOrg($q, $name)
    {
        return $q
            ->getInOrgRef()
            ->where('name', 'ILIKE', $name)
            ->firstOrFail();
    }
}
