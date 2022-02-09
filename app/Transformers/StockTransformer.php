<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Models\Stock;
use App\Transformers\Base\Transformer;

class StockTransformer extends Transformer
{

    const SIMPLE_FIELDS = [
        'stock_id',
        'item_id',
        'quantity',
        'notes',
    ];

    protected $availableIncludes = [
        'item'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform(Stock $model)
    {
        return $this->filterTransform([
            'stock_id' => $model->stock_id,
            'organization_id' => $model->organization_id,
            'item_id' => $model->item_id,
            'quantity' => $model->quantity,
            'notes' => $model->notes
        ]);
    }

    public function includeItem(Stock $stock)
    {
        $item = $stock->item;

        if (!is_null($item)) {
            return $this->item($item, ItemTransformer::inst()->showFields(
                $this->includeFields['item'] ?? []
            ));
        }

        return $this->null();
    }
}