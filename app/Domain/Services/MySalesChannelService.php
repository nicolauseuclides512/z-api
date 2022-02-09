<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Domain\Services;

use App\Domain\Contracts\MySalesChannelContract;
use App\Domain\Contracts\SalesChannelContract;
use App\Domain\Data\MySalesChannelData;
use App\Models\AuthToken;
use App\Models\MySalesChannel;
use App\Exceptions\AppException;
use App\Models\SalesChannel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MySalesChannelService implements MySalesChannelContract
{
    private $modelRepo;

    public function __construct(MySalesChannel $modelRepo)
    {
        $this->modelRepo = $modelRepo;
    }

    public function create(MySalesChannelData $data)
    {
        $model = new MySalesChannel();
        $model->fill($data->toArray());

        if (!$model->saveInOrganization()) {
            throw new AppException(
                "Failed to create My Sales Channel",
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $model->errors
            );
        }

        return $model;
    }

    public function update(MySalesChannelData $data)
    {
        $model = $this->modelRepo->getInOrgRef()
            ->where(
                'id',
                $data->getId()
            )->first();

        if (!$model) {
            throw new AppException(
                "My Sales Channel data does not exist",
                Response::HTTP_NOT_FOUND
            );
        }

        $model->fill($data->toArray());

        if (!$model->saveInOrganization()) {
            throw new AppException(
                "Failed to update My Sales Channel",
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $model->errors
            );
        }

        return $model;
    }

    public function delete($id)
    {
        $model = $this->modelRepo->getInOrgRef()
            ->where('id', $id)
            ->first();

        if (!$model) {
            throw new AppException(
                "My Sales Channel data does not exist",
                Response::HTTP_NOT_FOUND
            );
        }

        //TODO (seto): when implemented in sales order/other place
        //make sure no refernce of currently deleted object

        $model->forceDelete();
    }

    public function detail($id)
    {
        $model = $this->modelRepo->with('sales_channel')
            ->getInOrgRef()
            ->where('id', $id)->first();

        if (!$model) {
            throw new AppException(
                "My Sales Channel data does not exist",
                Response::HTTP_NOT_FOUND
            );
        }
        return $model;
    }

    public function fetch(array $filterRequest)
    {
        $data = $this->modelRepo->with('sales_channel')
            ->filter(
                $filterRequest['filter_by'],
                $filterRequest['q']
            )->orderBy(
                $filterRequest['sort_column'],
                $filterRequest['sort_order']
            )->paginate($filterRequest["per_page"]);

        return $data;
    }

    public function homeShop(array $filterRequest)
    {
        $data = $this->modelRepo
            ->select('sales_channel_id', 'is_shown', 'store_name', 'order', 'external_link', 'organization_id')
            ->where('is_shown', true)
            ->orderBy('order', 'desc')
            ->with('sales_channel')
            ->filter(
                $filterRequest['filter_by'],
                $filterRequest['q']
            )->orderBy(
                $filterRequest['sort_column'],
                $filterRequest['sort_order']
            )->paginate($filterRequest["per_page"]);

        return $data;
    }

    public function all()
    {
        $data = $this->modelRepo->with('sales_channel')
            ->getInOrgRef()->get();
        return $data;
    }

    /**
     * @param bool $force
     * @throws AppException
     */
    public function setup($force = false)
    {
        //inject eloquent
        $defaultSalesChannel = SalesChannel::find(11); //office business

        try {
            $this->create(MySalesChannelData::new(
                new Request([
                    'sales_channel_id' => $defaultSalesChannel->id,
                    'store_name' => AuthToken::info()->organizationName,
                    'display_mode' => 1
                ])
            ));
        } catch (AppException $e) {
            throw $e;
        }

    }
}
