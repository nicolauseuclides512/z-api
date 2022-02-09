<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Transformers\Base\Transformer;

class AssetCategoryTransformer extends Transformer
{
    const SIMPLE_FIELDS = [
        'asset_category_id',
        'name'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return $this->filterTransform([
            'asset_category_id' => $model->asset_category_id,
            'organization_id' => $model->organization_id,
            'name' => $model->name,
            'category_status' => $model->category_status,
        ]);
    }
}