<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Models\BaseTraits;


use App\Exceptions\AppException;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait RestModelTrait
{
    /**
     * @param array $request
     * @return mixed
     * @throws Exception
     */
    public function storeExec(array $request)
    {
        DB::beginTransaction();
        try {
            $request['action'] = 'store';
            $data = $this->populate($request, $this->inst());
            if (!$data->save()) {
                DB::rollback();
                Log::error('save data failed.');
                throw AppException::inst(
                    trans('messages.save_failed'),
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    $data->errors ?? null);
            }
            DB::commit();
            return $data;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @param array $request
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function updateExec(array $request, int $id)
    {
        DB::beginTransaction();
        try {
            $dataInId = $this->getByIdRef($id)->firstOrFail();
            $request['action'] = 'update';
            $data = $this->populate($request, $dataInId);
            if (!$data->save()) {
                DB::rollback();
                Log::error('save data failed.');
                throw AppException::inst(
                    trans('messages.update_failed'),
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    $data->errors ?? null);
            }
            DB::commit();
            return $data;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function destroyExec(int $id)
    {
        DB::beginTransaction();
        try {
            $dataInId = $this->getByIdRef($id)->first();
            if (!$dataInId->delete()) {
                DB::rollback();
                Log::error('save data failed.');
                throw AppException::inst(
                    trans('messages.delete_failed'),
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    $data->errors ?? null);
            }
            DB::commit();
            return $dataInId;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function destroySomeExec(string $ids)
    {
        $data = array_map(function ($id) {
            $dataInId = $this->getByIdRef($id)->first();
            if (!empty($dataInId)) {
                return array('errors' => "data by id $id not found");
            }
            if (!$dataInId->delete()) {
                return $dataInId;
            }
            return $dataInId;
        }, explode(',', preg_replace('/\s+/', '', $ids)));

        return $data;
    }

    public function markAsExec($ids, $status)
    {
        // TODO: Implement markAsExec() method.
    }
}