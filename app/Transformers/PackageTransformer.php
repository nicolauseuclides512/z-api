<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Models\Contact;
use App\Transformers\Base\Transformer;

class PackageTransformer extends Transformer
{
    public static function inst()
    {
        return new self();
    }

    public function transform($model): array
    {
        return $this->filterTransform([
            'organization_id' => $model->organization_id,
            'date' => $model->date,
            'slip' => $model->slip,
            'internal_notes' => $model->internal_notes,
            'sales_order_id' => $model->sales_order_id,
            'package_status' => $model->package_status,
        ]);
    }
}