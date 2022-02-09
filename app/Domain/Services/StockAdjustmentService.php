<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Domain\Services;

use App\Domain\Contracts\DocumentCounterContract;
use App\Domain\Contracts\ReasonContract;
use App\Domain\Contracts\StockAdjustmentContract;
use App\Domain\Contracts\StockContract;
use App\Domain\Data\DocumentCounterSetupData;
use App\Domain\Data\ReasonSetupData;
use App\Domain\Data\StockAdjustmentData;
use App\Domain\Data\StockAdjustmentDetailData;
use App\Exceptions\AppException;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Transformers\StockAdjustmentTransformer;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class StockAdjustmentService implements StockAdjustmentContract
{
    private $counterService;
    private $reasonService;
    private $stockService;
    private $model;
    private $stockAdjustmentTransformer;

    public function __construct(
        DocumentCounterContract $counterService,
        ReasonContract $reasonService,
        StockContract $stockService,
        StockAdjustment $model,
        StockAdjustmentTransformer $stockAdjustmentTransformer
    )
    {
        $this->counterService = $counterService;
        $this->reasonService = $reasonService;
        $this->stockService = $stockService;
        $this->model = $model;
        $this->stockAdjustmentTransformer = $stockAdjustmentTransformer;
    }

    public function setup($force = false)
    {
        try {
            DB::beginTransaction();
            $this->setupReason($force);
            $this->setupCounter($force);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function setupCounter($force)
    {
        $setupData = new DocumentCounterSetupData(
            StockAdjustment::URI,
            StockAdjustment::NUMBERING_PREFIX
        );
        $this->counterService->setup($setupData, $force);
    }

    private function setupReason($force)
    {
        $adjustmentReasons = config('reasons.stock_adjustment.default_reasons');
        $setupData = new ReasonSetupData(
            StockAdjustment::REASON_CATEGORY,
            $adjustmentReasons
        );
        $this->reasonService->setup($setupData, $force);
    }

    public function getNumberPreview()
    {
        $documentNumber = $this->counterService
            ->getNumbering(StockAdjustment::URI, false);
        return $documentNumber;
    }

    public function create(StockAdjustmentData $data)
    {
        try {
            DB::beginTransaction();

            $model = new StockAdjustment();
            $model->fill($data->toArray());
            $documentNumber = $this->counterService
                ->getNumbering(StockAdjustment::URI, true);
            $model->stock_adjustment_number = $documentNumber;

            //for unknown reason, fill method won't access mutator
            $model->stock_adjustment_date = $data->getStockAdjustmentDate();

            if (!$model->saveInOrganization()) {
                throw new AppException(
                    "Failed to create stock adjustment",
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $model->errors
                );
            }

            $this->saveDetails($data, $model);

            if ($data->getIsApplied()) {
                $this->apply($data);
            }

            DB::commit();

            //need to eager load the relationship
            return $this->model
                ::with(['details.item', 'details.reason',])
                ->find($model->stock_adjustment_id);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function saveDetails(StockAdjustmentData $data, &$model)
    {
        foreach ($data->getDetails() as $item) {
            $this->validateItem($item);
            $itemModel = new StockAdjustmentDetail();
            $itemModel->fill($item->toArray());
            if (!$model->details()->save($itemModel)) {
                throw new AppException(
                    "Failed to create stock adjustment detail",
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $itemModel->errors
                );
            }
        }
    }

    public function update(StockAdjustmentData $data)
    {
        try {

            $existingModel = $this->model->getInOrgRef()
                ->where(
                    'stock_adjustment_id',
                    $data->getStockAdjustmentId()
                )->first();

            $this->validateExistingData($existingModel);

            DB::beginTransaction();

            //it's easier to delete all items first rather than
            //checking one by one
            $existingModel->details()->forceDelete();
            $existingModel->fill($data->toArray());

            //for unknown reason, fill method won't access mutator
            $existingModel->stock_adjustment_date = $data->getStockAdjustmentDate();
            if (!$existingModel->saveInOrganization()) {
                throw new AppException(
                    "Failed to create stock adjustment",
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $existingModel->errors
                );
            }
            $this->saveDetails($data, $existingModel);

            if ($data->getIsApplied()) {
                $this->apply($data);
            }

            DB::commit();

            //need to eager load the relationship
            return $this->model
                ::with('details')
                ->find($data->getStockAdjustmentId());

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function validateExistingData($existingModel)
    {
        $this->validateExists($existingModel);
        if ($existingModel->is_applied) {
            throw new AppException("Stock Adjustment already applied.",
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if ($existingModel->is_void) {
            throw new AppException("Stock Adjustment already void.",
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    private function validateExists($existingModel)
    {
        if (!$existingModel) {
            throw new AppException("Stock Adjustment data not found",
                Response::HTTP_NOT_FOUND
            );
        }
    }

    private function validateItem(StockAdjustmentDetailData $item)
    {
        if (!$item->isValidQty()) {
            throw new AppException("Adjustment values are invalid",
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $item
            );
        }
    }

    private function apply(StockAdjustmentData $data)
    {
        foreach ($data->getDetails() as $detail) {
            $this->stockService->adjust($detail);
        }
    }

    public function detail($id)
    {
        $existingModel = $this->model
            ::with('details')
            ->getInOrgRef()
            ->where(
                'stock_adjustment_id',
                $id
            )->first();
        $this->validateExists($existingModel);
        return $existingModel;
    }

    public function delete($id)
    {
        try {

            $existingModel = $this->model->getInOrgRef()
                ->where(
                    'stock_adjustment_id',
                    $id
                )->first();
            $this->validateExistingData($existingModel);

            DB::beginTransaction();
            $existingModel->details()->forceDelete();
            $existingModel->forceDelete();
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function fetch(array $filterRequest)
    {
        $data = $this->model->filter($filterRequest['filter_by'],
            $filterRequest['q'])
            ->orderBy(
                $filterRequest['sort_column'],
                $filterRequest['sort_order']
            )->paginate($filterRequest["per_page"]);

//        return $data;

        return $this->stockAdjustmentTransformer
            ->showFields($this->stockAdjustmentTransformer::HISTORY_FIELDS)
            ->includeRelations(['stock_adjustment_details'])
            ->createCollectionPageable($data);
    }

    public function getHistoryByItem(array $filterRequest)
    {
        $data = $this->model
            ->byItem($filterRequest['q'])
            ->orderBy(
                $filterRequest['sort_column'],
                $filterRequest['sort_order']
            )->paginate($filterRequest["per_page"]);


        return $this->stockAdjustmentTransformer
            ->showFields($this->stockAdjustmentTransformer::HISTORY_FIELDS)
            ->includeRelations(['stock_adjustment_details'])
            ->createCollectionPageable($data);
    }

    public function getHistoryByReason(array $filterRequest)
    {
        $data = $this->model
            ->byReason($filterRequest['q'])
            ->orderBy(
                $filterRequest['sort_column'],
                $filterRequest['sort_order']
            )->paginate($filterRequest["per_page"]);

        return $data;
    }
}
