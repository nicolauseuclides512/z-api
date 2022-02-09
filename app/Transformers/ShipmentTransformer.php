<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;


use App\Transformers\Base\Transformer;

class ShipmentTransformer extends Transformer
{
    const SIMPLE_FIELDS = [
        'shipment_order_number',
        'date',
        'carrier_id',
        'tracking_number',
        'notes',
        'is_delivered',
        'package_id',
        'carrier'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return $this->filterTransform([
            'organization_id' => $model->organization_id,
            'shipment_order_number' => $model->shipment_order_number,
            'date' => $model->date,
            'carrier_id' => $model->carrier_id,
            'tracking_number' => $model->tracking_number,
            'notes' => $model->notes,
            'is_delivered' => $model->is_delivered,
            'package_id' => $model->package_id,
            'carrier' => $model->carrier ?? []
        ]);
    }
}