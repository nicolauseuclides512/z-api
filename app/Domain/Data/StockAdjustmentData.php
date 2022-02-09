<?php

namespace App\Domain\Data;

/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Arrayable;

class StockAdjustmentData implements Arrayable
{
    protected $stockAdjustmentId;
    protected $stockAdjustmentNumber;
    protected $stockAdjustmentDate;
    protected $referenceNumber;
    protected $isApplied;
    protected $isVoid;
    protected $details;
    protected $isFreeAdjust;
    protected $notes = "";

    protected function __construct(
        $stockAdjustmentId,
        $stockAdjustmentNumber,
        $stockAdjustmentDate,
        $referenceNumber,
        $notes,
        $isApplied,
        $isVoid,
        $isFreeAdjust = false
    )
    {
        $this->stockAdjustmentId = $stockAdjustmentId;
        $this->stockAdjustmentNumber = $stockAdjustmentNumber;
        $this->stockAdjustmentDate = $stockAdjustmentDate;
        $this->referenceNumber = $referenceNumber;
        $this->notes = $notes;
        $this->isApplied = $isApplied;
        $this->isVoid = $isVoid;
        $this->isFreeAdjust = $isFreeAdjust;
    }

    public function getStockAdjustmentId()
    {
        return $this->stockAdjustmentId;
    }

    public function getStockAdjustmentNumber()
    {
        return $this->stockAdjustmentNumber;
    }

    public function getStockAdjustmentDate()
    {
        return $this->stockAdjustmentDate;
    }

    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function getIsApplied()
    {
        return $this->isApplied;
    }

    public function getIsVoid()
    {
        return $this->isVoid;
    }

    public function getIsFreeAdjust()
    {
        return $this->isFreeAdjust;
    }

    public function getDetails()
    {
        if (is_null($this->details)) {
            return [];
        }
        return $this->details;
    }

    public function toArray()
    {
        return [
            'stock_adjustment_id' => $this->stockAdjustmentId,
            'stock_adjustment_number' => $this->stockAdjustmentNumber,
            'stock_adjustment_date' => $this->stockAdjustmentDate,
            'reference_number' => $this->referenceNumber,
            'notes' => $this->notes,
            'is_applied' => $this->isApplied,
            'is_void' => $this->isVoid,
            'is_free_adjust' => $this->isFreeAdjust,
            'details' => array_map(function ($item) {
                return $item->toArray();
            }, $this->getDetails()),
        ];
    }

    public static function fromModel(StockAdjustment $model)
    {
        $data = new static (
            $model->stock_adjustment_id,
            $model->stock_adjustment_number,
            $model->stock_adjustment_date,
            $model->reference_number,
            $model->notes,
            $model->is_applied,
            $model->is_void,
            $model->is_free_adjust
        );
        $data->details = StockAdjustmentDetailData::fromModelArray($model->details);
        return $data;
    }

    public static function new(Request $request, $skipDetail = false)
    {
        $data = new static (
            null,
            null,
            $request->get('stock_adjustment_date'),
            $request->get('reference_number'),
            $request->get('notes'),
            $request->get('is_applied') === 'true' ? true : false,
            $request->get('is_void') === 'true' ? true : false
        );
        if (!$skipDetail) {
            $data->details = StockAdjustmentDetailData::newArray($request);
        }
        return $data;
    }

    public static function update(
        $stockAdjustmentId,
        Request $request
    )
    {
        $data = new static (
            $stockAdjustmentId,
            $request->get('stock_adjustment_number'),
            $request->get('stock_adjustment_date'),
            $request->get('reference_number'),
            $request->get('notes'),
            $request->get('is_applied') === 'true' ? true : false,
            $request->get('is_void') === 'true' ? true : false
        );
        $data->details = StockAdjustmentDetailData::newArray($request);
        return $data;
    }
}
