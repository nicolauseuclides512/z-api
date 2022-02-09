<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Models\AssetUom;
use App\Models\ItemMedia;
use App\Models\ItemRate;
use App\Transformers\Base\Transformer;

class ItemMediaTransformer extends Transformer
{

    const SIMPLE_FIELDS = [
        'item_media_id',
        'item_id',
        'media_type',
        'is_resized',
        'is_main',
        'media_url',
        'item_media_status',
        'multi_res_image'
    ];

    protected $availableIncludes = [
        'item'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform(ItemMedia $model)
    {
        return $this->filterTransform([
            'item_media_id' => $model->item_media_id,
            'item_id' => $model->item_id,
            'media_type' => $model->media_type,
            'is_resized' => $model->is_resized,
            'is_main' => $model->is_main,
            'media_url' => $model->media_url,
            'item_media_status' => $model->item_media_status,
            'multi_res_image' => $model->multi_res_image->toArray()
        ]);
    }

    public function includeItem(ItemMedia $itemMedia)
    {
        $item = $itemMedia->item;

        if (!is_null($item)) {
            return $this->item($item, ItemTransformer::inst()->showFields(
                $this->includeFields['item'] ?? []
            ));
        }

        return $this->null();
    }

}