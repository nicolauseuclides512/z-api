<?php
/**
 * Created by PhpStorm.
 * User: nicolaus
 * Date: 20/08/18
 * Time: 14:26
 */

namespace App\Http\Controllers;


use App\Exceptions\AppException;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\PatternController;
use App\Http\Controllers\Base\RestFulControl;
use App\Models\SalesChannel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SalesChannelController extends BaseController implements PatternController
{
    use RestFulControl;

    public $configName = "sales_channel";

    public $requiredParamFetch = [
        'id',
        'channel_name',
    ];

    protected $sortBy = [
        "id",
        "channel_name"
    ];

    public function __construct(Request $request)
    {
        parent::__construct(
            SalesChannel::inst(),
            $request,
            $useAuth = true);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function remove($id = null)
    {
        try {

            $data = $this->model->getByIdRef($id)->firstOrFail();

            if (!$data->forceDelete()) {
                AppException::inst('Delete data failed.');
            }

            Log::info($this->configName . " with id " . $id .
                " successfully deleted");

            return $this->json(
                Response::HTTP_OK,
                $this->configName . " with id " . $id . " successfully deleted",
                $data
            );

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function edit($id = null)
    {
        try {
            $data = $this->model
                ->getByIdRef($id)
                ->firstOrFail();

            Log::info("Get " . $this->configName . " by id " . $id);

            $resource = $this->_resource();
            $resource[$this->configName] = $data;

            return $this->json(
                Response::HTTP_OK,
                "get " . $this->configName . " by id " . $id,
                $resource);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }
}