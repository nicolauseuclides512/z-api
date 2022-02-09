<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Transformers\Base\Transformer;

class AssetTaxTransformer extends Transformer
{
    const SIMPLE_FIELDS = [
        'tax_id',
        'name',
        'percent'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return $this->filterTransform([
            'tax_id' => $model->tax_id,
            'organization_id' => $model->organization_id,
            'name' => $model->name,
            'percent' => $model->percent,
            'tax_status' => $model->tax_status,
        ]);
    }
}