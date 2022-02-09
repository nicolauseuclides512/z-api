<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Support\Collection;

class ItemRate extends MasterModel
{

    protected $table = 'item_rates';

    protected $primaryKey = 'item_rate_id';

    protected $columnStatus = 'item_rate_status';

    protected $columnDefault = array("*");

    protected $columnSimple = array("*");


    public function item()
    {
        return $this->belongsTo(Item::class, 'item_rate_id');
    }

   public function rules($id = null)
    {
        return array(
            'item_id' => 'required|integer',
            'rate' => 'required|numeric|between:0,9999999999',
            'quantity' => 'required|integer|min:1'
        );
    }

    public static function inst()
    {
        return new ItemRate();
    }

    public function populate($request = array(), BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);

        $model->item_id = $req->get('item_id');
        $model->rate = $req->get('rate');
        $model->quantity = $req->get('quantity');

        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();

        return $data;
    }

    public function scopeFindInItemRef($q, $item_id)
    {
        return $q->where('item_id', $item_id);
    }
}