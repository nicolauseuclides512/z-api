<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Domain\Services;

use App\Domain\Contracts\StockContract;
use App\Domain\Contracts\AdjustStockContract;
use App\Domain\Contracts\StockKeyContract;
use App\Exceptions\AppException;
use App\Models\Setting;
use App\Models\Stock;
use App\Domain\Contracts\ItemRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class StockService implements StockContract
{
    private $model;
    private $itemRepo;

    public function __construct(Stock $model, ItemRepository $itemRepo)
    {
        $this->model = $model;
        $this->itemRepo = $itemRepo;
    }

    public function adjust(AdjustStockContract $param)
    {
        try {

            DB::beginTransaction();
            $stockItem = $this->model->getInOrgRef()
                ->where('item_id', $param->getItemId())
                ->first();

            if (!$stockItem) {
                $stockItem = new Stock();
                $stockItem->item_id = $param->getItemId();
                $stockItem->quantity = 0;
                $stockItem->notes = '';
            }

            $adjustQty = $param->getAdjustQty();
            $itemId = $param->getItemId();

            $this->validateItem($itemId);

            $allow_zero_stock = (boolean)Setting::findByKeyInOrg('web.checkout.allow_out_of_stock_order')->value;
            if ($adjustQty < 0 && !$allow_zero_stock) {
                if ($stockItem->quantity < ((-1) * $adjustQty)) {
                    throw new AppException(
                        "Insufficient stock",
                        Response::HTTP_UNPROCESSABLE_ENTITY
                    );
                }
            }

            $stockItem->quantity += $adjustQty;
            $stockItem->saveInOrganization();
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function validateItem($itemId)
    {
        $item = $this->itemRepo->getItemInOrg($itemId);
        if (!$item) {
            throw new AppException(
                "Item does not exist",
                Response::HTTP_NOT_FOUND
            );
        }
        if (!$item->track_inventory) {
            throw new AppException(
                sprintf("Item ID: %d, inventory tracking is disabled.",
                    $item->item_id),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    public function detail(StockKeyContract $param)
    {
        $itemId = $param->getItemId();
        $stockDetail = $this->model
            ->with('item')
            ->getInOrgRef()
            ->where('item_id', $itemId)
            ->first();

        return $stockDetail;
    }

    public function fetch(array $modifiedRequest)
    {
        $data = $this->model
            ->with('item')
            ->filter(
                $modifiedRequest['filter_by'],
                $modifiedRequest['q']
            )->orderBy(
                $modifiedRequest['sort_column'],
                $modifiedRequest['sort_order']
            )->paginate($modifiedRequest["per_page"]);

        return $data;
    }
}
