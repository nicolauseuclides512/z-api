<?php
/**
 * Created by PhpStorm.
 * User: nicolaus
 * Date: 19/08/18
 * Time: 22:21
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Support\Collection;

class QuickReply extends MasterModel
{
    protected $table = 'quick_replies';

    protected $primaryKey = 'quick_reply_id';

    protected $fillable = ["name", "description", "category_id", "organization_id"];

    protected $columnDefault = array("quick_reply_id", "name", "description", "category_id");

    protected $columnSimple = array("quick_reply_id", "name", "description", "category_id");

    public static function inst()
    {
        return new self();
    }

    public function __construct()
    {
        parent::__construct();
        $this->nestedBelongConfigs = array(
            "quick_reply_category" => QuickReplyCategory::inst()->getColumnSimple()
        );
    }

    protected $softDeleteCascades = [];

    public function populate($request = [], BaseModel $model = null)
    {
        $req = new Collection($request);

        if (is_null($model))
            $model = self::inst();

        $model->organization_id = AuthToken::info()->organizationId;
        $model->name = $req->get('name');
        $model->description = $req->get('description');
        $model->category_id = $req->get('category_id');

        return $model;
    }

    public function rules($id = null)
    {
        $uniqueHandler = $id ? ',' . $id . ',quick_reply_id' : '';

        return [
            'organization_id' => 'required|integer',
            'name' => 'required|string|max:100|org_unique:quick_replies,name' . $uniqueHandler,
            'description' => 'required|string|max:100',
            'category_id' => 'nullable|integer|exists:quick_reply_categories,category_id|in_organization:quick_reply_categories,' . $this->getOrganizationId() . ',category_id'
        ];
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();

        switch (true) {
            case $filterBy === self::STATUS_ACTIVE :
                $data = $data->where('status', true);
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