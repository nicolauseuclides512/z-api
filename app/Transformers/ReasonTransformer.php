<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Transformers\Base\Transformer;

class ReasonTransformer extends Transformer
{
    const SIMPLE_FIELDS = [
        'reason_id',
        'category_code',
        'reason'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return $this->filterTransform([
            'reason_id' => $model->reason_id,
            'organization_id' => $model->organization_id,
            'category_code' => $model->category_code,
            'reason' => $model->reason,
        ]);
    }
}