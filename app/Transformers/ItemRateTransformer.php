<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Models\AssetUom;
use App\Models\ItemRate;
use App\Transformers\Base\Transformer;

class ItemRateTransformer extends Transformer
{

    const SIMPLE_FIELDS = [
        'item_rate_id',
        'item_id',
        'rate',
        'quantity'
    ];

    protected $availableIncludes = [
        'item'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform(ItemRate $model)
    {
        return $this->filterTransform([
            'item_rate_id' => $model->item_rate_id,
            'item_id' => $model->item_id,
            'rate' => $model->rate,
            'quantity' => $model->quatity,
        ]);
    }

    public function includeItem(ItemRate $itemRate)
    {
        $item = $itemRate->item;

        if (!is_null($item)) {
            return $this->item($item, ItemTransformer::inst()->showFields(
                $this->includeFields['item'] ?? []
            ));
        }

        return $this->null();
    }
}