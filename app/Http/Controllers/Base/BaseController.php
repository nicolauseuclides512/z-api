<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Http\Controllers\Base;

use App\Cores\Jsonable;
use App\Cores\RequestMod;
use App\Models\AuthToken;
use App\Services\Gateway\Base\BaseServiceContract;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

/**
 * Class BaseController
 * @package App\Http\Controllers
 */
abstract class BaseController extends Controller
{

    use Jsonable, RequestMod;

    protected $transformer;

    public $configName = "";

//    public $statusField = "account_status";

    public $requiredFilter = array();

    public $requiredParamFetch = array();

    public $requiredParamStore = array();

    public $requiredParamMark = array();

    public $useNestedOnList = false;

    protected $model = null;

    protected $request = null;

    protected $sortBy = ['created_at'];

    protected $service;

    /**
     * BaseController constructor.
     * @param $modelName
     * @param Request $request
     * @param bool $useAuth
     * @param BaseServiceContract $service
     * @void
     */
    public function __construct($modelName,
                                Request $request,
                                $useAuth = false,
                                BaseServiceContract $service = null)
    {
        $this->model = $modelName;
        $this->request = $request;
        if ($service) {
            $this->service = $service;
            $this->service->setBaseUri(env('GATEWAY_ASSET_API'));
        }

        if ($useAuth) {
            $this->model->setLoginInfo(AuthToken::$info);
        }
    }

    /**
     * @return null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

}
