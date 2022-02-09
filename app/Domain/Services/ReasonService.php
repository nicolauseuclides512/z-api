<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Domain\Services;

use App\Domain\Contracts\ReasonContract;
use App\Domain\Data\ReasonSetupData;
use App\Domain\Data\ReasonData;
use App\Exceptions\AppException;
use App\Models\Reason;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReasonService implements ReasonContract
{
    private $modelRepo;

    public function __construct(Reason $modelRepo)
    {
        $this->modelRepo = $modelRepo;
    }

    private function isExist($categoryCode)
    {
        return $this->modelRepo->getInOrgRef()
            ->where('category_code', $categoryCode)
            ->exists();
    }

    private function deleteAllInCategory($categoryCode)
    {
        $this->modelRepo->getInOrgRef()
            ->where('category_code', $categoryCode)
            ->forceDelete();
    }


    public function setup(ReasonSetupData $data, $force = false)
    {
        try {
            $exist = $this->isExist($data->getCategoryCode());

            if ($exist && !$force) {
                \Log::info('Reason: Already exist, no need to setup');
                return;
            }

            DB::beginTransaction();

            if ($exist && $force) {
                \Log::info('Reason: Forced setup');
                $this->deleteAllInCategory($data->getCategoryCode());
            }

            foreach ($data->getDefaultReasons() as $defaultReason) {
                $reason = new Reason();
                $reason->category_code = $data->getCategoryCode();
                $reason->reason = $defaultReason;
                if (!$reason->saveInOrganization()) {
                    $errMsg = 'Reason: setup failed';
                    throw new AppException(
                        $errMsg,
                        Response::HTTP_INTERNAL_SERVER_ERROR,
                        $reason->errors
                    );
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getByCategory($reasonCategory)
    {
        return $this->modelRepo->getByCategory($reasonCategory)->get();
    }

    public function getAdjustmentReasons()
    {
        return $this->getByCategory(config('reasons.stock_adjustment.category'));
    }

    /**
     * @param ReasonData $data
     * @return Reason
     * @throws AppException
     */
    public function create(ReasonData $data)
    {
        $model = new Reason();
        $model->fill($data->toArray());

        if (!$model->saveInOrganization()) {
            throw new AppException(
                "Failed to create Reason",
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $model->errors
            );
        }

        return $model;
    }

    /**
     * @param $id
     * @return mixed
     * @throws AppException
     */
    public function detail($id)
    {
        $model = $this->modelRepo
            ->getInOrgRef()
            ->where('reason_id', $id)->first();

        if (!$model) {
            throw new AppException(
                "Reason data does not exist",
                Response::HTTP_NOT_FOUND
            );
        }
        return $model;
    }

    /**
     * @param ReasonData $data
     * @return mixed
     * @throws AppException
     */
    public function update(ReasonData $data)
    {
        $model = $this->modelRepo->getInOrgRef()
            ->where(
                'reason_id',
                $data->getId()
            )->first();

        if (!$model) {
            throw new AppException(
                "Reason data does not exist",
                Response::HTTP_NOT_FOUND
            );
        }

        $model->fill($data->toArray());

        if (!$model->saveInOrganization()) {
            throw new AppException(
                "Failed to update Reason",
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $model->errors
            );
        }

        return $model;
    }

    /**
     * @param $id
     * @throws AppException
     */
    public function delete($id)
    {
        $model = $this->modelRepo->getInOrgRef()
            ->where('reason_id', $id)
            ->first();

        if (!$model) {
            throw new AppException(
                "Reason data does not exist",
                Response::HTTP_NOT_FOUND
            );
        }

        //TODO (seto): when implemented in sales order/other place
        //make sure no refernce of currently deleted object

        $model->forceDelete();
    }
}
