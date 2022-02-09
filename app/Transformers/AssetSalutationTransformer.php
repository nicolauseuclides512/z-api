<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */


namespace App\Transformers;


use App\Models\AssetSalutation;
use App\Transformers\Base\Transformer;

class AssetSalutationTransformer extends Transformer
{
    const SIMPLE_FIELDS = [
        'salutation_id',
        'name'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform(AssetSalutation $model)
    {
        return $this->filterTransform([
            'salutation_id' => $model->salutation_id,
            'organization_id' => $model->organization_id,
            'name' => $model->name,
            'salutation_status' => $model->salutation_status,
        ]);
    }
}