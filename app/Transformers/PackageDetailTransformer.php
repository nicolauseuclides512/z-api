<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Transformers\Base\Transformer;

class PackageDetailTransformer extends Transformer
{

    const SIMPLE_FIELDS = [];

    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return $this->filterTransform([
            'package_detail_id' => $model->package_detial_id,
            'sales_order_detail_id' => $model->sales_order_detail_id,
            'quantity' => $model->quantity,
            'package_id' => $model->package_id
        ]);
    }
}