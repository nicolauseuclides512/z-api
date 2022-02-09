<?php

namespace App\Http\Controllers;

use App\Domain\Contracts\DocumentCounterContract;
use App\Domain\Contracts\ShipmentContract;
use App\Exceptions\AppException;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\PatternController;
use App\Http\Controllers\Base\RestFulControl;
use App\Models\AuthToken;
use App\Models\Package;
use App\Models\SalesOrder;
use App\Models\Shipment;
use App\Services\Gateway\Base\BaseServiceContract;
use App\Services\Gateway\Rest\RestService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * class AccountController
 */
class ShipmentController extends BaseController implements PatternController
{
    use RestFulControl;

    public $configName = "shipment";

    public $statusField = "is_delivered";

    public $requiredFilter = array();

    protected $sortBy = array(
        "created_at",
        "updated_at"
    );

    public $requiredParamFetch = array();

    public $requiredParamStore = array("date", "carrier_id", "tracking_number");

    public $requiredParamMark = array("active", "inactive");

    private $counterService;
    private $shipmentService;

    public function __construct(
        Request $request,
        BaseServiceContract $service,
        DocumentCounterContract $counterService,
        ShipmentContract $shipmentService
    )
    {
        parent::__construct($modelName = Shipment::inst(), $request,
            $useAuth = true, $service);
        $this->counterService = $counterService;
        $this->shipmentService = $shipmentService;
    }

    /**
     * @param $soId
     * @return array
     * @throws Exception
     * @throws \Throwable
     */
    public function _resource($soId)
    {

        $so = SalesOrder::inst()->getByIdInOrgRef($soId)->firstOrFail();
        $nextNumber = $this->getNextShipmentOrderNumber(false);
        $carriers = [];
        if (!empty($so->shipping_carrier_code)) {
            $carrier = $this->service->getCarrierByCode($so->shipping_carrier_code);
            $carrier->service = $so->shipping_carrier_service;
        } else {
            $carrier = $this->service->getCarrierByCode('custom');
        }

        array_push($carriers, $carrier);
        return [
            "carrier" => $carriers,
            "next_shipment_order_number" => $nextNumber,
        ];

    }

    public function fetch($soId = null)
    {
        $data = $this->model;

        $pkg = Package::inst()->getBySalesOrderId($soId)->first();

        if (!$pkg)
            return $this->json(
                Response::HTTP_OK,
                'skip get shipment cause Package not found');
//            throw AppException::inst('package not found', Response::HTTP_BAD_REQUEST);

        if ($this->useNestedOnList) {
            $data = $data->nested();
        }

        $data = $data->filter($this->requestMod()['filter_by'], $this->requestMod()['q'])
            ->where('package_id', $pkg->package_id)
            ->orderBy(
                $this->requestMod()['sort_column'],
                $this->requestMod()['sort_order'])
            ->paginate($this->request->input("per_page"));

        if (!empty($data)) {
            Log::info($this->configName . " fetched");
            return $this->json(
                Response::HTTP_OK,
                $this->configName . " fetched.",
                $data);
        }

        Log::error($this->configName . " Not Found");
        return $this->json(
            Response::HTTP_BAD_REQUEST,
            $this->configName . " Not Found",
            $data);
    }

    /**
     * @param null $soId
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws Exception
     * @throws \Throwable
     */
    public function create($soId = null)
    {
        return $this->json(
            Response::HTTP_OK,
            "get create shipment resource",
            $this->_resource($soId)
        );
    }

    public function edit($id = null, $soId = null)
    {
        try {
            Log::info("Get " . $this->configName . " by id " . $id);

            $data = $this->getModel()->getByIdInOrgRef($id)->firstOrFail();

            $resource[$this->configName] = $data;

            return $this->json(
                Response::HTTP_OK,
                "get " . $this->configName . " by id " . $id,
                $resource);

        } catch (Exception  $e) {
            return $this->jsonExceptions($e);
        }
    }

    private function getNextShipmentOrderNumber($commit = false)
    {
        $this->shipmentService->setup(false);
        $nextId = $this->counterService->getNumbering(Shipment::URI, $commit);
        return $nextId;
    }

    //TODO(jee): sementara bypass create multiple shipment
    public function store(Request $request, $soId = null)
    {
        try {
            DB::beginTransaction();

            $model = $this->getModel();

            $req = $request->input();

            $req['action'] = 'store';

            $salesOrder = $salesOrder = SalesOrder::inst()
                ->getByIdInOrgRef($soId)
                ->firstOrFail();

            $salesOrder->makeVisible('sales_order_details');

            $packageBody = [
                'date' => $req['date'],
                'slip' => 'PKG' . str_random(10),
                'internal_notes' => $salesOrder->internal_notes,
                'sales_order_id' => $salesOrder->sales_order_id,
                'package_status' => 'ACCEPTED', //status dianggap accepted
                'package_details' => $salesOrder->sales_order_details
            ];

            $packageData = Package::inst()->getBySalesOrderId($soId)->first();

            if ($packageData) {
                throw AppException::inst(
                    'Package exist on sales order ' . $soId,
                    Response::HTTP_BAD_REQUEST);
            }

            $packageRes = Package::inst()->storeExec($packageBody);

            if (is_null($packageRes)) {
                DB::rollback();
                Log::error('error when save package');
                throw AppException::inst(
                    trans('messages.required_fields'),
                    Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            /*harusnya proses save ini udah sekalian save details*/
//            $req['is_delivered'] = false;
            $req['package_id'] = $packageRes->package_id;
            $req['shipment_order_number'] = $this->getNextShipmentOrderNumber(true);

            $shipment = $model->populate($req);

            if (!$shipment->save()) {
                DB::rollback();
                Log::error($this->configName . " hasn't been created", $shipment->toArray());
                throw AppException::inst(
                    trans('messages.required_fields'),
                    Response::HTTP_BAD_REQUEST,
                    $shipment
                );
            };

            //so
            $salesOrder->setStatus(SalesOrder::FULFILLED);

            $salesOrder->shipment_date = $shipment->date;
            $salesOrder->save();

            DB::commit();
            $successMsg = trans('messages.shipment_created');

            Log::info($successMsg);
            return $this->json(
                Response::HTTP_CREATED,
                $successMsg,
                $shipment
            );

        } catch (\Exception  $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function update($id = null, Request $request, $soId = null)
    {
        try {

            DB::beginTransaction();

            $shipmentModel = $this->getModel();

            $req = $request->input();

            $req['action'] = 'update';

            $salesOrder = SalesOrder::inst()
                ->getByIdInOrgRef($soId)
                ->firstOrFail();

            $existingShipment = $shipmentModel
                ->getByIdInOrgRef($id)
                ->firstOrFail();

            if (!empty($existingShipment) && !empty($salesOrder)) {
                /*update shipment and package*/

//                $req['is_delivered'] = $existingShipment->is_delivered;
                $req['package_id'] = $existingShipment->package_id;
                $shipment = $shipmentModel->populate($req, $existingShipment);

                if (!$shipment->save()) {
                    DB::rollback();
                    Log::error($this->configName . " hasn't been update");

                    throw AppException::flash(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        trans('messages.update_shipment_failed'),
                        $shipment->errors);
                }

                $salesOrder->shipment_date = $shipment->date;
                $salesOrder->save();

                DB::commit();
                Log::info($this->configName . " has been update");
                return $this->json(
                    Response::HTTP_CREATED,
                    trans('messages.shipment_updated'),
                    $shipment);

            }

            DB::rollback();
            Log::error($this->configName . " hasn't been update, Sales order and shipment does not exists.");

            throw AppException::flash(
                Response::HTTP_BAD_REQUEST,
                'Sales order and shipment does not exists.'
            );

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    /**
     * hapus data by array ids
     * ga make transaction karena diharapkan skip data yang ga bisa dihapus
     * @param Request $request
     * @param null $soId
     * @return mixed
     */
    public function destroy(Request $request, $soId = null)
    {
        try {
            $input = $request->get('ids');

            if (empty($input)) {
                throw AppException::inst('param ids not found.', Response::HTTP_BAD_REQUEST);
            }

            $ids = explode(',',
                preg_replace('/\s+/', '', $input));

            $delDataList = array_map(function ($id) use ($soId) {
                DB::beginTransaction();
                $shipment = $this->getModel()
                    ->getByIdInOrgRef($id)
                    ->first();

                if (empty($shipment)) {
                    DB::rollback();
                    Log::error($this->configName . " with id " . $id . " doesn't exist");
                    throw AppException::flash(
                        Response::HTTP_NOT_FOUND,
                        "The $this->configName id $id does not exist.");
                }

                if ($shipment->is_delivered === true) {
                    DB::rollback();
                    Log::info($this->configName . " with id " . $id . " can't be deleted");
                    throw AppException::flash(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        "The $this->configName id $id is delivered.");
                }

                if (!$shipment->forceDelete()) {
                    DB::rollback();
                    Log::info($this->configName . " with id " . $id . "  can't be deleted");
                    throw AppException::flash(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        "The $this->configName id $id is already in used.",
                        $shipment->errors);
                }

                //TODO (jee) : blm handle partial
                $package = Package::inst()
                    ->getByIdInOrgRef($shipment->package_id)
                    ->firstOrFail();

                if (!$package->forceDelete()) {
                    DB::rollback();
                    Log::info($this->configName . " with id " . $id . " can't be deleted");
                    throw AppException::flash(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        "The package does not exist or is already in used.");
                }

                $salesOrder = SalesOrder::inst()
                    ->getByIdInOrgRef($soId)
                    ->firstOrFail();

                //Set sales order status back to awaiting shipment
                $salesOrder->setStatus(
                    SalesOrder::AWAITING_SHIPMENT
                );

                $salesOrder->shipment_date = null;
                $salesOrder->save();

                DB::commit();
                Log::info("THe shipment id " . $id . "deleted");

                return [
                    "id" => (int)$id,
                    'message' => $this->configName . " with id " . $id . " has been successfully deleted"
                ];

            }, $ids);

            if (in_array(Response::HTTP_BAD_REQUEST, array_column($delDataList, 'id'))) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    $this->configName . " doesn't exist");
            }

            return $this->json(
                Response::HTTP_OK,
                trans('messages.shipment_deleted'),
                $delDataList);

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function downloadPackageListAndShipmentLabel(Request $request)
    {
        try {

            if ($request->get('ids')) {

                $pop = [];

                $ids = explode(',', $request->get('ids'));

                foreach ($ids as $k => $v) {

                    $shipment = $this->getModel()->getByIdInOrgRef($v)->nested()->first();

                    if (!empty($shipment)) {

                        $area = RestService::inst()->getArea(
                            $shipment->package->sales_order->shipping_country,
                            $shipment->package->sales_order->shipping_province,
                            $shipment->package->sales_order->shipping_district,
                            $shipment->package->sales_order->shipping_region
                        );

                        $shipment->package->sales_order->shipping_country_name
                            = !empty($area) && isset($area->country)
                            ? $area->country->name : '';
                        $shipment->package->sales_order->shipping_province_name
                            = !empty($area) && isset($area->province)
                            ? $area->province->name : '';
                        $shipment->package->sales_order->shipping_district_name
                            = !empty($area) && isset($area->district)
                            ? $area->district->name : '';
                        $shipment->package->sales_order->shipping_region_name
                            = !empty($area) && isset($area->region)
                            ? $area->region->name : '';

                        array_push($pop, [
                            'shipment' => $shipment
                        ]);
                    }
                }

                $pdf = App::make('snappy.pdf.wrapper');

                $organization = AuthToken::info();

                $area = RestService::inst()->getArea(
                    $organization->countryId,
                    $organization->provinceId,
                    $organization->districtId,
                    $organization->regionId
                );

                $organization->country_name = !empty($area) && isset($area->country) ? $area->country->name : '';
                $organization->province_name = !empty($area) && isset($area->province) ? $area->province->name : '';
                $organization->district_name = !empty($area) && isset($area->district) ? $area->district->name : '';
                $organization->region_name = !empty($area) && isset($area->region) ? $area->region->name : '';

                $pdf->loadView('shipment.shipment_label', ['data' => $pop, 'organization' => $organization]);

                $pdf->setPaper('a4')
                    ->setOrientation('portrait')
                    ->setOption('margin-bottom', 0);

                header('Content-Type: application/pdf');
                header('X-Frame-Options: NONE');

                return $pdf->inline();
            }

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        } catch (\Throwable $e) {
            return $this->jsonExceptions($e);
        }
    }
}
