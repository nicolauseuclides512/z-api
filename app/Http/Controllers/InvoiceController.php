<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\PatternController;
use App\Http\Controllers\Base\RestFulControl;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Services\Gateway\Base\BaseServiceContract;
use App\Transformers\InvoiceTransformer;
use App\Transformers\SalesOrderTransformer;
use Exception;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * class InvoiceController
 */
class InvoiceController extends BaseController implements PatternController
{
    use RestFulControl;

    public $configName = "invoice";
    public $requiredFilter = array();

    protected $sortBy = array(
        "created_at",
        "updated_at",
        "order_date",
        "invoice_number",
        "customer_name",
        "due_date",
        "amount"
    );

    public $requiredParamFetch = array();

    public $requiredParamStore = array("name", "account_status");

    public $requiredParamMark = array("active", "inactive");

    public function __construct(Request $request,
                                BaseServiceContract $service,
                                InvoiceTransformer $invoiceTransformer)
    {
        parent::__construct(
            Invoice::inst(),
            $request,
            $useAuth = true,
            $service);

        $this->service = $service;
        $this->service->setBaseUri(env('GATEWAY_ASSET_API'));
        $this->transformer = $invoiceTransformer;
    }

    public function getInvoiceBySoId($soId)
    {
        try {
            $data = Invoice::inst($this->getModel()->getLoginInfo())
                ->getBySalesOrderId($soId)
                ->get();

            $invoice = $this
                ->transformer
                ->showFields(InvoiceTransformer::SORT_FIELDS)
                ->includeRelations(['invoice_details', 'contact', 'sales_order'])
                ->showIncludeFields(['sales_order' => SalesOrderTransformer::SORT_FIELDS])
                ->createCollection($data);

            return $this->json(
                Response::HTTP_OK,
                "get invoices in sales order",
                $invoice);

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function getInvoiceByIdAndSoId($soId = null, $invId = null)
    {
        try {
            $data = Invoice::inst($this->getModel()->getLoginInfo())
                ->getByIdAndSalesOrderId($soId, $invId)
                ->nested()
                ->firstOrFail();

            return $this->json(Response::HTTP_OK, trans('messages.invoice_details_fetched'), $data);
        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function sendInvoiceEmailByIdAndSoId($soId = null, $invId = null, Request $request)
    {
        try {
            $inst = Invoice::inst();
            $invoice = $inst
                ->getByIdAndSalesOrderId($soId, $invId)
                ->nested()
                ->firstOrFail();

            return $inst->sendPDFEmail($invoice, $request->input())
                ? $this->json(Response::HTTP_OK, trans('messages.email_sent'))
                : $this->json(Response::HTTP_INTERNAL_SERVER_ERROR, trans('messages.email_not_sent'));
        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function sendInvoiceEmailByIdAndSoIdInDetail($soId = null, $invId = null)
    {
        try {
            $inst = Invoice::inst();
            $invoice = $inst
                ->getByIdAndSalesOrderId($soId, $invId)
                ->nested()
                ->firstOrFail();

            $sendStatus = $inst->sendPDFEmail($invoice);

            Log::info('send invoice status ' . $sendStatus);

            return $sendStatus
                ? $this->json(Response::HTTP_OK, trans('messages.email_sent'))
                : $this->json(Response::HTTP_INTERNAL_SERVER_ERROR, trans('messages.email_not_sent'));

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function generatePDFInvoiceByIdAndSoId($soId = null, $invId)
    {
        try {
            $invoiceIns = Invoice::inst();

            $invoice = $invoiceIns
                ->getByIdAndSalesOrderId($soId, $invId)
                ->nested()
                ->firstOrFail();


            $getFileUrl = $this->request->get('get_file_url') ?: false;

            Log::debug('generate pdf' . $getFileUrl);

            $result = $invoiceIns->generatePDF($invoice, $getFileUrl);

            if ($getFileUrl)
                return $this->json(
                    Response::HTTP_OK,
                    "generate pdf url",
                    $result);

            return $result;

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        } catch (\Throwable $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function generateBulkPDF(Request $request)
    {
        try {

            if (!$request->get('ids')) {
                return AppException::inst(
                    'ids param not found.',
                    Response::HTTP_BAD_REQUEST);
            }

            $arrSoId = explode(',', $request->get('ids'));

            $getFileUrl = $this->request->get('get_file_url') ?: false;
            Log::debug('generate pdf' . $getFileUrl);

            $result = $this->model->generateBulkPDF($arrSoId, $getFileUrl);

            if ($getFileUrl)
                return $this->json(
                    Response::HTTP_OK,
                    "generate bulk pdf url",
                    $result);

            return $result;
        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function markAsSent($soId = null, $invId)
    {
        try {
            $invoice = Invoice::inst()
                ->getByIdAndSalesOrderId($soId, $invId)
                ->firstOrFail();

            if ($invoice->invoice_status == Invoice::SENT) {
                throw AppException::inst(trans('messages.convert_to_invoice_failed'));
            }

            if ($invoice->invoice_status == Invoice::PARTIALLY_PAID ||
                $invoice->invoice_status == Invoice::PAID) {
                throw AppException::inst(trans('messages.convert_to_invoice_failed'));
            }

            $callback = Invoice::inst()->setStatus($invoice->invoice_id, Invoice::SENT);

            if (!$callback) {
                throw AppException::inst(Response::HTTP_INTERNAL_SERVER_ERROR,
                    'Something gone wrong.');
            }

            return $this->json(Response::HTTP_CREATED, trans('messages.convert_to_invoice_succeeded'));

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function markAsVoid($soId = null, $invId)
    {
        try {
            $invoice = Invoice::inst($this->getModel()->getLoginInfo())
                ->getByIdAndSalesOrderId($soId, $invId)
                ->firstOrFail();

            if ($invoice->invoice_status == Invoice::VOID) {
                throw AppException::inst('This invoice is already void.');
            }

            Log::info($invoice->invoice_status);
//            if ($invoice->invoice_status == Invoice::PAID ||
//                $invoice->invoice_status == Invoice::PARTIALLY_PAID) {
//                throw AppException::inst('This invoice cannot mark as void');
//            }

            $callback = Invoice::inst()->setStatus($invoice->invoice_id, Invoice::VOID);

            if (!$callback) {
                throw AppException::inst(Response::HTTP_INTERNAL_SERVER_ERROR,
                    'Something gone wrong.');
            }

            return $this->json(Response::HTTP_CREATED,
                trans('messages.invoice_voided'));


        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function _popArea($data)
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

        $data['billing_country_detail'] = $billingArea->country;
        $data['billing_province_detail'] = $billingArea->province;
        $data['billing_district_detail'] = $billingArea->district;
        $data['billing_region_detail'] = $billingArea->region;

        $shippingArea = json_decode($res['shippingArea']->getBody())->data ?? [];

        $data['shipping_country_detail'] = $shippingArea->country;
        $data['shipping_province_detail'] = $shippingArea->province;
        $data['shipping_district_detail'] = $shippingArea->district;
        $data['shipping_region_detail'] = $shippingArea->region;

        return $data;
    }
}
