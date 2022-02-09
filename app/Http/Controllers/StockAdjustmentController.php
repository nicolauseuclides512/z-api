<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Http\Controllers;

use App\Cores\Jsonable;
use App\Cores\HasFilterRequest;
use App\Domain\Contracts\ReasonContract;
use App\Domain\Contracts\StockAdjustmentContract;
use App\Domain\Commands;
use App\Domain\Data\StockAdjustmentData;
use App\Domain\Data\StockAdjustmentDetailData;
use App\Exceptions\AppException;
use App\Transformers\StockAdjustmentTransformer;
use Arseto\LumenCQRS\CommandBus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller;

class StockAdjustmentController extends Controller
{
    use Jsonable;
    use HasFilterRequest;

    private $reasonService;
    private $stockAdjustmentService;
    private $stoctAdjustmentTransformer;

    protected $filterCfg;

    protected $sortBy = [
        "stock_adjustments.created_at",
        "stock_adjustments.updated_at",
        "stock_adjustment_date",
        "status",
        "reference_number",
        "stock_adjustment_number",
    ];

    public function __construct(
        ReasonContract $reasonService,
        StockAdjustmentContract $stockAdjustmentService,
        StockAdjustmentTransformer $stockAdjustmentTransformer
    )
    {
        $this->reasonService = $reasonService;
        $this->stockAdjustmentService = $stockAdjustmentService;
        $this->filterCfg = config('filters.stock_adjustments');
        $this->stoctAdjustmentTransformer = $stockAdjustmentTransformer;
    }

    public function setup(Request $request)
    {
        try {
            $force = parseBool($request->get('force'));
            $this->stockAdjustmentService->setup($force);

            return $this->json(Response::HTTP_OK,
                'Stock Adjustment setup success' . ($force ? ' (forced).' : '.'));
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function create()
    {
        try {
            $reasons = $this->reasonService->getAdjustmentReasons();
            $nextNumber = $this->stockAdjustmentService->getNumberPreview();

            return $this->json(Response::HTTP_OK,
                'Resource for stock adjustment loaded.', [
                    'reasons' => $reasons,
                    'next_stock_adjustment_number' => $nextNumber,
                ]);
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }


    public function store(Request $request)
    {
        try {

            $itemIds = array_column($request->get('details'), 'item_id');

            if($itemIds == null){
                throw AppException::inst(trans('messages.no_item_selected'),
                Response::HTTP_BAD_REQUEST,
                    ['message' => [trans('messages.no_item_selected')]]
                );
            }

            if (count($itemIds) !== count(array_unique($itemIds))) {
                throw AppException::inst(
                    "The request has a duplicate item record.",
                    Response::HTTP_BAD_REQUEST,
                    ['item_id' => ['duplicate record.']]);
            }

            $data = StockAdjustmentData::new($request);
            $result = $this->stockAdjustmentService->create($data);

            return $this->json(Response::HTTP_CREATED,
                trans('messages.stock_adjustment_created'),
                $result
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $data = StockAdjustmentData::update($id, $request);
            $result = $this->stockAdjustmentService->update($data);

            return $this->json(Response::HTTP_CREATED,
                'Stock adjustment updated',
                $result
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function detail($id)
    {
        try {
            $result = $this->stockAdjustmentService->detail($id);

            return $this->json(Response::HTTP_OK,
                'Stock adjustment loaded',
                $result
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function delete($id)
    {
        try {
            $this->stockAdjustmentService->delete($id);

            return $this->json(Response::HTTP_OK,
                'Stock adjustment deleted'
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function fetch(Request $request)
    {
        try {
            $filterRequest = $this->translateFilterRequest(
                $request,
                $this->sortBy,
                $this->filterCfg
            );
            $result = $this->stockAdjustmentService->fetch($filterRequest);
//            $result = $this->stockAdjustmentService->getHistoryByReason($filterRequest);

            return $this->json(Response::HTTP_OK,
                'Stock adjustment fetched',
                $result,
                $filterRequest
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function itemHistory(Request $request)
    {
        try {
            $filterRequest = $this->translateFilterRequest(
                $request,
                $this->sortBy,
                $this->filterCfg
            );

            $result = $this->stockAdjustmentService->getHistoryByItem($filterRequest);

            return $this->json(Response::HTTP_OK,
                'Item history fetched',
                $result,
                $filterRequest
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function reasonHistory(Request $request)
    {
        try {
            $filterRequest = $this->translateFilterRequest(
                $request,
                $this->sortBy,
                $this->filterCfg
            );
            $result = $this->stockAdjustmentService->getHistoryByReason($filterRequest);

            return $this->json(Response::HTTP_OK,
                'Reason history fetched',
                $result,
                $filterRequest
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }
}
