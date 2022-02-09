<?php

namespace App\Http\Controllers;

use App\Cores\Variable;
use App\Domain\Contracts\DocumentCounterContract;
use App\Domain\Contracts\MySalesChannelContract;
use App\Domain\Contracts\SalesOrderContract;
use App\Exceptions\AppException;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\PatternController;
use App\Http\Controllers\Base\RestFulControl;
use App\Models\Image;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\Setting;
use App\Services\Gateway\Base\BaseServiceContract;
use App\Transformers\InvoiceTransformer;
use App\Transformers\SalesOrderDetailTransformer;
use App\Transformers\SalesOrderTransformer;
use Exception;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


/**
 * class AccountController
 */
class SalesOrderController extends BaseController implements PatternController
{
    use RestFulControl;

    public $configName = "sales_order";

    protected $sortBy = [
        "created_at",
        "updated_at",
        "invoice_date",
        "sales_order_status",
        "term_date",
        "shipment_date"
        //"sales_order_date",
        //"invoice_number",
        //"display_name",
        //"total"
    ];

    public $requiredFilter = [];

    public $requiredParamFetch = [];

    public $requiredParamStore = ["contact_id"];

    public $requiredParamMark = ["active", "inactive"];

    public $useNestedOnList = true;

    private $myScService;

    private $counterService;

    public function __construct(Request $request,
                                BaseServiceContract $service,
                                MySalesChannelContract $myScService,
                                DocumentCounterContract $counterService,
                                SalesOrderContract $salesOrderService,
                                SalesOrderTransformer $salesOrderTransformer)
    {
        parent::__construct(
            $modelName = SalesOrder::inst(),
            $request,
            $useAuth = true,
            $service);

        $this->myScService = $myScService;
        $this->counterService = $counterService;
        $this->salesOrderService = $salesOrderService;
        $this->transformer = $salesOrderTransformer;
    }

    private function getNextSalesOrderNumber($commit = false)
    {
        $this->salesOrderService->setup(false);
        $nextNumber = $this->counterService->getNumbering(
            SalesOrder::URI, $commit
        );
        return $nextNumber;
    }

    /**
     * fungsi untuk mengambil kebutuhan data ketika create dan edit
     * @return array
     */
    public function _resource()
    {
        $weightUnit = Variable::WEIGHTS;
        $discountUnit = Variable::DISCOUNT_UNIT;
        $taxIncluded = Setting::reFormatOutput()['web.item.price.tax_included'];
        $channels = $this->myScService->all();
        $nextNumber = $this->getNextSalesOrderNumber();

        return array(
            'weight_unit' => $weightUnit,
            'discount_unit' => $discountUnit,
            'tax_included' => $taxIncluded,
            'my_sales_channels' => $channels,
            'next_sales_order_number' => $nextNumber
        );
    }

    public function fetch()
    {
        try {
            $data = $this->model;

            $data = $data->filter(
                $this->requestMod()['filter_by'],
                $this->requestMod()['q'])
                ->orderBy(
                    $this->requestMod()['sort_column'],
                    $this->requestMod()['sort_order'])
                ->paginate($this->request->input("per_page"));

            if (!$data) {
                Log::error($this->configName . " Not Found");
                return $this->json(
                    Response::HTTP_NOT_FOUND,
                    $this->configName . " Not Found");
            }

            $fields = !empty($this->request->get('fields'))
                ? explode(',', preg_replace(
                        '/\s+/',
                        '',
                        $this->request->get('fields'))
                ) : [];

            $salesOrder = $this->transformer
                ->showFields($fields ?: SalesOrderTransformer::SORT_FIELDS)
                ->includeRelations($fields ?: ['invoices', 'contact', 'my_sales_channel'])
                ->showIncludeFields([
                    'invoices' => InvoiceTransformer::SORT_FIELDS,
                    'contact' => ['display_name', 'contact_id']
                ])
                ->createCollectionPageable($data);

            Log::info($this->configName . " fetched");
            return $this->json(Response::HTTP_OK,
                $this->configName . " fetched.", $salesOrder);


        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function store(Request $request)
    {
        try {
            return $this->json(
                $code = Response::HTTP_CREATED,
                $message = trans('messages.so_created'),
                $model_response = $this->getModel()->storeData($request->input())
            );

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function update($id = null, Request $request)
    {
        try {
            return $this->json(
                $code = Response::HTTP_CREATED,
                $message = trans('messages.so_updated'),
                $model_response = $this->getModel()->updateData($id, $request->input())
            );

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $input = $request->get('ids');

            if (empty($input)) {
                throw AppException::inst(
                    "param id not found",
                    Response::HTTP_BAD_REQUEST);
            }

            $ids = explode(',', preg_replace('/\s+/', '', $input));
            $delDataList = array_map(function ($id) {
                DB::beginTransaction();
                $data = $this->getModel()->getByIdInOrgRef($id)->first();

                if (!empty($data)) {
                    if ($data->delete()) {
                        $invoiceData = Invoice::inst($this->getModel()->getLoginInfo())
                            ->getBySalesOrderId($data->sales_order_id)
                            ->first();

                        if ($invoiceData) {
                            DB::commit();
                            $invoiceData->delete();
                        }
                        DB::rollback();
                        Log::info($this->configName . " with id " . $id . " successfully deleted");
                        return array("id" => $id, 'message' => $this->configName . " with id " . $id . " successfully deleted");
                    }
                    DB::rollback();
                    Log::error($this->configName . " with id " . $id . "cannot be deleted");
                    return array("id" => -1, "message" => $data['errors']);
                }
                DB::rollback();
                Log::error($this->configName . " with id " . $id . " doesn't exist");
                return array("id" => -1, "message" => $this->configName . " id " . $id . " in this Organisation doesn't exist");
            }, $ids);

            if (!in_array(-1, array_column($delDataList, 'id'))) {
                return $this->json(Response::HTTP_OK, "deleted " . $this->configName, $delDataList);
            }

            return $this->json(Response::HTTP_BAD_REQUEST, $this->configName . " doesn't exist");
        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function getCredential()
    {
        $cred = Image::inst()->setLoginInfo($this->getModel()->getLoginInfo())->getCredential('sales_order');
        return $this->json(Response::HTTP_OK, "get upload credential sales order.", $cred);
    }

    /**
     * fungsi untuk mengambil data list detail dari sales order
     * berdasarkan sales order id
     * @param null $soId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDetails($soId = null)
    {
        try {
            $data = SalesOrderDetail::inst()
                ->getBySalesOrderId($soId)
                ->get();

            $sod = SalesOrderDetailTransformer::inst()
                ->showFields(SalesOrderDetailTransformer::SORT_FIELDS)
                ->createCollection($data);

            return $this->json(
                Response::HTTP_OK,
                "get sales order detail",
                $sod);

        } catch (Exception $e) {
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

            $fields = !empty($this->request->get('fields'))
                ? explode(',', preg_replace(
                        '/\s+/',
                        '',
                        $this->request->get('fields'))
                ) : [];

            $data = $this
                ->model
                ->getByIdInOrgRef($id)
                ->firstOrFail();

            $so = $this
                ->transformer
                ->showFields($fields ?: SalesOrderTransformer::SORT_FIELDS)
                ->includeRelations($fields ?: ['sales_order_details', 'my_sales_channel'])
                ->showIncludeFields([
                    'contact' => ['display_name', 'contact_id'],
                    'sales_order_details' => SalesOrderDetailTransformer::SORT_FIELDS
                ])
                ->excludeRelations(['invoices'])
                ->createItem($this->_popDetailRelation($data));

            Log::info("Get " . $this->configName . " by id " . $id);
            return $this->json(Response::HTTP_OK, "get " . $this->configName .
                " by id " . $id, $so);

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

            if (empty($data)) {
                Log::error($this->configName . " with id " . $id . " not found");
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    $this->configName . " with id " . $id . " not found");
            }

            Log::info("Get " . $this->configName . " by id " . $id);

            $resource = $this->_resource();
            $resource['sales_order'] = $this->_popDetailRelation($data);

            return $this->json(
                Response::HTTP_OK,
                "get " . $this->configName . " by id " . $id,
                $resource
            );

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function _popDetailRelation($data)
    {
        $promise = [
            'billingArea' => $this->service->getAsync('/countries/areas',
                [
                    'countryId' => $data->billing_country,
                    'provinceId' => $data->billing_province,
                    'districtId' => $data->billing_district,
                    'regionId' => $data->billing_region,
                ]
            ),
            'shippingArea' => $this->service->getAsync('/countries/areas',
                [
                    'countryId' => $data->shipping_country,
                    'provinceId' => $data->shipping_province,
                    'districtId' => $data->shipping_district,
                    'regionId' => $data->shipping_region,
                ]
            ),
        ];

        $res = Promise\unwrap($promise);

        $billingArea = json_decode($res['billingArea']->getBody())->data ?? [];

        $data['billing_country_detail'] = $billingArea->country ?? null;
        $data['billing_province_detail'] = $billingArea->province ?? null;
        $data['billing_district_detail'] = $billingArea->district ?? null;
        $data['billing_region_detail'] = $billingArea->region ?? null;

        $shippingArea = json_decode($res['shippingArea']->getBody())->data ?? null;

        $data['shipping_country_detail'] = $shippingArea->country ?? null;
        $data['shipping_province_detail'] = $shippingArea->province ?? null;
        $data['shipping_district_detail'] = $shippingArea->district ?? null;
        $data['shipping_region_detail'] = $shippingArea->region ?? null;

        return $data;
    }

    /**
     * TODO (seto)
     */
    public function updateDetail($soId, $detailId, Request $request)
    {
        try {
            $this->validateUpdateDetail($request);
            $input = $request->only('item_rate');

            $so = $this->model->find($soId);
            if (!$so) {
                $msg = "Sales Order not found.";
                \Log::warning($msg);
                throw new AppException($msg,
                    Response::HTTP_NOT_FOUND
                );
            }
            $detail = $so->sales_order_details()->find($detailId);
            if (!$detail) {
                $msg = "Sales Order Detail not found.";
                \Log::warning($msg);
                throw new AppException($msg,
                    Response::HTTP_NOT_FOUND
                );
            }
            $detail->fill($input);
            $detail->save();

            return $this->json(Response::HTTP_CREATED,
                "Detail updated", $detail);
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    /**
     * @param Request $request
     * @throws AppException
     */
    private function validateUpdateDetail(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'item_rate' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            throw AppException::inst("Invalid update item data.",
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $validator->errors()
            );
        }
    }

    public function generateShipmentLabelBulkPDF(Request $request)
    {
        try {
            if (!$request->get('ids')) {
                return AppException::inst('ids param not found.', Response::HTTP_BAD_REQUEST);
            }

            $ids = explode(',', $request->get('ids'));

            return $this->model->generateShipmentLabelBulkPDF($ids);
        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

}
