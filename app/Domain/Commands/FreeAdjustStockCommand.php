<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Domain\Commands;

use App\Domain\Data\StockAdjustmentData;
use App\Domain\Contracts\StockKeyContract;
use Arseto\LumenCQRS\Contracts\Command;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

class FreeAdjustStockCommand
    extends StockAdjustmentData implements Command, Arrayable, StockKeyContract
{

    protected function __construct(
        $stockAdjustmentId,
        $stockAdjustmentNumber,
        $stockAdjustmentDate,
        $referenceNumber,
        $notes,
        $isApplied,
        $isVoid
    )
    {
        parent::__construct(
            $stockAdjustmentId,
            $stockAdjustmentNumber,
            $stockAdjustmentDate,
            $referenceNumber,
            $notes,
            $isApplied,
            $isVoid,
            true
        );
    }

    public static function fromRequest(Request $request)
    {
        $data = new static (
            null,
            null,
            date('Y-m-d'),
            '',
            '',
            true,
            false
        );
        $detail = FreeAdjustStockDetailCommand::fromRequest($request);
        $data->details = [
            $detail,
        ];
        return $data;
    }

    public function getItemId()
    {
        return $this->details[0]->getItemId();
    }

    public function merge($databaseQty)
    {
        $this->details[0]->merge($databaseQty);
    }
}
