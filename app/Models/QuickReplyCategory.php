<?php
/**
 * Created by PhpStorm.
 * User: nicolaus
 * Date: 8/15/18
 * Time: 9:40 AM
 */

namespace App\Models;


use App\Models\Base\BaseModel;
use Illuminate\Support\Collection;

class QuickReplyCategory extends MasterModel
{
    protected $table = 'quick_reply_categories';

    protected $primaryKey = 'category_id';

    protected $columnDefault = array("category_id", "title");

    protected $columnSimple = array("category_id", "title");

    public static $info;

    public static function inst()
    {
        return new self();
    }

    public function autotexts(){
        return $this->hasMany(QuickReply::class, 'category_id');
    }

    public function rules($id = null)
    {
        $uniqueHandler = $id ? ',' . $id . ',category_id' : '';

        return array(
            'organization_id' => 'required|integer',
            'name' => 'required|string|max:50|org_unique:quick_reply_categories,name'. $uniqueHandler
        );
    }

    public function populate($request = [], BaseModel $model = null)
    {
        $req = new Collection($request);

        if(is_null($model))
            $model = self::inst();

        $model->organization_id = AuthToken::info()->organizationId;
        $model->name = $req->get('name');

        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();

        switch (true) {
            case $filterBy === self::STATUS_ACTIVE :
                $data = $data->where('category_status', true);
                break;
            case $filterBy === self::STATUS_INACTIVE :
                $data = $data->where($this->columnStatus, false);
                break;
        }

        if (!empty($key)) {
            $data = $data->where("name", "ILIKE", "%" . $key . "%");
        }

        return $data;
    }
}