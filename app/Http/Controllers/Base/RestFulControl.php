<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Http\Controllers\Base;


use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * restful controller,
 * kode ini akan override default restful fungsi jika dipakai di controller
 * USAGE :
 * tambahkan perintah berikut di controller :
 * use RestFulTrait;
 * public $configName = "";
 * public $requiredFilter = array();
 * protected $sortColumn = array();
 * public $requiredParamFetch = array();
 * public $requiredParamStore = array();
 * public $requiredParamMark = array();
 */
trait RestFulControl
{
    /**
     * @return mixed
     * @internal param Request $request
     */
    public function fetch()
    {
        try {
            $data = $this->model;

            if ($this->useNestedOnList) {
                $data = $data->nested();
            }

            $data = $data->filter($this->requestMod()['filter_by'],
                $this->requestMod()['q'])
                ->orderBy(
                    $this->requestMod()['sort_column'],
                    $this->requestMod()['sort_order'])
                ->paginate($this->request->input("per_page"));

            if (!empty($data)) {
                Log::info($this->configName . " fetched");
                return $this->json(Response::HTTP_OK,
                    $this->configName . " fetched.", $data);
            }

            Log::error($this->configName . " Not Found");
            throw AppException::flash(
                Response::HTTP_BAD_REQUEST,
                $this->configName . " Not Found",
                $data);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }


    /**
     * @param null $id
     * @return mixed
     */
    public function detail($id = null)
    {
        try {
            $data = $this->model
                ->getByIdInOrgRef($id)
                ->nested()
                ->firstOrFail();

            Log::info("Get " . $this->configName . " by id " . $id);
            return $this->json(
                Response::HTTP_OK,
                "get " . $this->configName . " by id " . $id,
                $data);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        try {
            $successMsg = $this->configName . " created";
            Log::info($successMsg);
            return $this->json(
                Response::HTTP_CREATED,
                $successMsg,
                $this->model->storeExec($request->all())
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    /**
     * @param null $id
     * @param Request $request
     * @return mixed
     */
    public function update($id = null, Request $request)
    {
        try {
            $successMsg = $this->configName . " updated";
            Log::info($successMsg);

            return $this->json(
                Response::HTTP_CREATED,
                $successMsg,
                $this->model->updateExec($request->all(), $id)
            );

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function destroy(Request $request)
    {
        try {
            $input = $request->get('ids');

            if (empty($input)) {
                throw AppException::inst(
                    trans('messages.param_ids_not_found'),
                    Response::HTTP_BAD_REQUEST);
            }

            $ids = explode(',', preg_replace('/\s+/', '', $input));

            $delDataList = array_map(function ($id) {
                $data = $this->model->getByIdInOrgRef($id)->first();

                if (!empty($data)) {
                    if ($data->delete()) {
                        Log::info($this->configName . " with id " . $id .
                            " successfully deleted");
                        return [
                            "id" => $id,
                            'message' => $this->configName . " with id " . $id . " successfully deleted"
                        ];
                    }
                    Log::error($this->configName . " with id " . $id .
                        "cannot be deleted");
                    return [
                        "id" => -1,
                        "message" => $data['errors']
                    ];
                }
                Log::error($this->configName . " with id " . $id . " doesn't exist");
                return [
                    "id" => -1,
                    "message" => $this->configName . " id " . $id . " in this Organisation doesn't exist"
                ];

            }, $ids);

            $successMsg = $this->configName . " deleted";

            if (!in_array(-1, array_column($delDataList, 'id'))) {
                return $this->json(
                    Response::HTTP_OK,
                    $successMsg,
                    $delDataList);
            }

            throw AppException::flash(
                Response::HTTP_BAD_REQUEST,
                $this->configName . " doesn't exist");
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function markAs($status, Request $request)
    {

        try {
            DB::beginTransaction();

            $input = $request->input('ids');
            $ids = explode(',', preg_replace('/\s+/', '', $input));
            $statusCode = $this->model->filterCfg()[$status];
            $dataList = array_map(function ($id) use ($statusCode, $status) {
                $data = $this->model->getByIdInOrgRef($id)->first();

                if (!empty($data)) {
                    //set active/inactive
                    $data->{$this->statusField} = $statusCode;

                    if ($data->save()) {

                        $children = $data->children;
                        $childrenRes = [];

                        if (!empty($children) && !$children->isEmpty()) {

                            $childrenRes = $children->map(function ($child) use (
                                $statusCode
                            ) {
                                $child->{$this->statusField} = $statusCode;
                                $child->save();
                                return $child;
                            });

                            if (!$childrenRes->isEmpty() &&
                                !$childrenRes->pluck(
                                    'errors')->filter()->isEmpty()
                            ) {
                                DB::rollBack();

                                throw new AppException(
                                    "Some " . $this->configName .
                                    " has not been marked, we will rollback",
                                    Response::HTTP_BAD_REQUEST,
                                    $childrenRes);
                            }
                        }

                        Log::info($this->configName . " with id " . $id . " successfully set " . $status);
                        return [
                            "id" => $id,
                            'message' =>
                                (!empty($childrenRes)) ?
                                    'with child ids [' . $childrenRes->map(function ($x) {
                                        return $x->item_id . ',';
                                    }) . ']' : '' . $this->configName .
                                    " successfully " . $status];

                    }

                    DB::rollBack();
                    Log::error($this->configName . " with id " . $id .
                        " cannot be set to " . $status);
                    return array("id" => -1, "message " => $data['errors']);
                }
                DB::rollBack();
                Log::error($this->configName . " with id " . $id . " doesn't exist");
                return array("id" => -1, "message" => $this->configName . " id " .
                    $id . " in this Organisation doesn't exist");

            }, $ids);

            if (!in_array(-1, array_column($dataList, 'id'))) {
                DB::commit();
                return $this->json(
                    Response::HTTP_CREATED,
                    "$this->configName successfully set " . $status,
                    $dataList);
            }
            DB::rollback();
            return $this->json(
                Response::HTTP_BAD_REQUEST,
                "Some $this->configName failed to set " . $status,
                $dataList);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }


    public function edit($id = null)
    {
        try {
            $data = $this->model
                ->getByIdInOrgRef($id)
                ->nested()
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

    public function create()
    {
        try {

            return $this->json(
                Response::HTTP_OK,
                "get create resource ",
                $this->_resource());

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function _resource()
    {
        return [];
    }

    public function list()
    {
        try {
            return $this->json(
                Response::HTTP_OK,
                $this->configName . " fetched.",
                $this->model->get()
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }
}
