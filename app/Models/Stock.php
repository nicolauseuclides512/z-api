<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Events\StockUpdated;
use Illuminate\Support\Collection;

class Stock extends MasterModel
{

    protected $table = 'stocks';

    protected $primaryKey = 'stock_id';

    protected $columnDefault = ["*"];

    protected $columnSimple = ["*"];

    public function rules($id = null)
    {
        return [
            'organization_id' => 'required|integer',
            'item_id' => 'integer|exists:items,item_id',
            'quantity' => 'integer',
            'notes' => 'string',
        ];
    }

    public static function inst()
    {
        return new Stock();
    }

    public function populate($request = [], BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);
        $model->organization_id = AuthToken::info()->organizationId;
        $model->item_id = $req->get('item_id');
        $model->quantity = $req->get('quantity');
        $model->notes = $req->get('notes');

        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();

        if (!empty($key)) {
            $data = $data
                ->whereHas('item', function ($query) use ($key) {
                    return $query->where('item_name', 'ILIKE', "%$key%")
                        ->orWhere("description", 'ILIKE', "%$key")
                        ->orWhere('code_sku', 'ILIKE', "%$key");
                });
        }

        return $data;
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public static function boot()
    {
        parent::boot();

        static::saved(function (Stock $model) {
            event(new StockUpdated($model));
        });
    }
}
