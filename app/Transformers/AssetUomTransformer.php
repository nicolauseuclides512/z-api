<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Models\AssetUom;
use App\Transformers\Base\Transformer;

class AssetUomTransformer extends Transformer
{

    const SIMPLE_FIELDS = [
        'asset_uom_id',
        'name'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform(AssetUom $model)
    {
        return $this->filterTransform([
            'asset_oum_id' => $model->asset_uom_id,
            'organization_id' => $model->organization_id,
            'name' => $model->name,
            'description' => $model->description,
            'uom_status' => $model->uom_status,
            'is_default' => $model->is_default,
        ]);
    }
}