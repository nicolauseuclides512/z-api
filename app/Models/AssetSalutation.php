<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Support\Collection;

class AssetSalutation extends MasterModel
{
    const DEFAULT_COLUMN = array("salutation_id", "name");

    protected $table = 'asset_salutations';

    protected $primaryKey = 'salutation_id';

    protected $columnStatus = 'salutation_status';

    protected $columnDefault = array("salutation_id", "name");

    protected $columnSimple = array("salutation_id", "name");

    protected $showActiveOnly = true;

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'salutation_id');
    }

    public function rules($id = null)
    {
        return [
            'name' => 'required|string',
            'salutation_status' => 'required|boolean'
        ];
    }

    public static function inst()
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
        $model->salutation_status = (int)$req->get('salutation_status');
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

}