<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;


use App\Models\StockAdjustment;
use App\Transformers\Base\Transformer;

class StockAdjustmentTransformer extends Transformer
{
    const HISTORY_FIELDS = [
        'stock_adjustment_date',
        'stock_adjustment_id',
        'stock_adjustment_number',
        'reference_number',
        'item_id',
        'item_name',
        'reason',
        'adjust_qty',
        'on_hand_qty',
        'is_void',
        'is_applied',
        'status',
        'reason_summary',
        'notes'
    ];

    protected $availableIncludes = [
        'stock_adjustment_details'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return $this->filterTransform([
            'stock_adjustment_number' => $model->stock_adjustment_number,
            'stock_adjustment_date' => $model->stock_adjustment_date,
            'reference_number' => $model->reference_number,
            'is_applied' => $model->is_applied,
            'is_void' => $model->is_void,
            'status' => $model->status,
            'reason_summary' => $model->reason_summary,
            'stock_adjustment_id' => $model->stock_adjustment_id,
            'item_id' => $model->item_id,
            'item_name' => $model->item_name,
            'on_hand_qty' => $model->on_hand_qty,
            'adjust_qty' => $model->adjust_qty,
            'reason' => $model->reason,
            'notes' => $model->notes,
        ]);
    }

    public function includeStockAdjustmentDetails(StockAdjustment $adjustment)
    {
        $detail = $adjustment->details;

        if (!is_null($detail)) {
            return $this->collection($detail, StockAdjustmentDetailTransformer::inst()
                ->showFields($this->includeFields['stock_adjustment_details'] ?? [])
            );
        }

        return $this->null();
    }
}