<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */


namespace App\Transformers;

use App\Transformers\Base\Transformer;

class AssetPaymentTermTransformer extends Transformer
{
    const SIMPLE_FIELDS = [
        'payment_term_id',
        'name',
        'day'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return $this->filterTransform([
            'payment_term_id' => $model->payment_term_id,
            'organization_id' => $model->organization_id,
            'name' => $model->name,
            'day' => $model->day,
            'description' => $model->description,
            'payment_term_status' => $model->payment_term_status,
            'is_default' => $model->is_default
        ]);
    }
}