<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */


namespace App\Transformers;

use App\Models\StockAdjustmentDetail;
use App\Transformers\Base\Transformer;

class StockAdjustmentDetailTransformer extends Transformer
{

    protected $defaultIncludes = [
        'item',
        'reason'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return $this->filterTransform([
            'item_id' => $model->item_id,
            'reason_id' => $model->reason_id,
            'database_qty' => $model->database_qty,
            'adjust_qty' => $model->adjust_qty,
            'on_hand_qty' => $model->on_hand_qty,
        ]);
    }

    public function includeItem(StockAdjustmentDetail $detail)
    {
        $item = $detail->item;

        if (!is_null($item)) {
            return $this->item($item, ItemTransformer::inst()
                ->showFields($this->includeFields['item'] ?? ['item_name'])
            );
        }

        return $this->null();
    }

    public function includeReason(StockAdjustmentDetail $detail)
    {
        $item = $detail->reason;

        if (!is_null($item)) {
            return $this->item($item, ReasonTransformer::inst()
                ->showFields($this->includeFields['reason'] ?? [])
            );
        }

        return $this->null();
    }


}