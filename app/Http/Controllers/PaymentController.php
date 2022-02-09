<?php

namespace App\Http\Controllers;

use App\Domain\Contracts\DocumentCounterContract;
use App\Domain\Contracts\PaymentContract;
use App\Exceptions\AppException;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\PatternController;
use App\Http\Controllers\Base\RestFulControl;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/**
 * class AccountController
 */
class PaymentController extends BaseController implements PatternController
{
    use RestFulControl;

    public $configName = "Payment";

    public $requiredFilter = array();

    protected $sortBy = array(
        "created_at",
        "updated_at"
    );

    public $requiredParamFetch = array();

    public $requiredParamStore = array("date", "payment_mode_id", "currency", "amount");

    public $requiredParamMark = array("active", "inactive");

    public $useNestedOnList = true;

    private $invoiceService;
    private $counterService;
    private $paymentService;

    public function __construct(
        Request $request,
        Invoice $invoiceService,
        DocumentCounterContract $counterService,
        PaymentContract $paymentService
    )
    {
        $this->invoiceService = $invoiceService;
        $this->counterService = $counterService;
        $this->paymentService = $paymentService;

        parent::__construct($modelName = Payment::inst(), $request, $useAuth = true);
    }

    public function _resource($soId = null, $invId = null)
    {
        try {
            $inv = Invoice::inst($this->getModel()->getLoginInfo())->getByIdAndSalesOrderId($soId, $invId)->nested()->firstOrFail();
            $nextNumber = $this->getNextPaymentNumber(false);

            return [
                'payment_method' => Setting::reformatKeyOutput('web.payments'),
                'total_payment' => $inv->total,
                'due_payment' => $inv->balance_due,
                'next_payment_number' => $nextNumber
            ];
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function fetch($invId = null)
    {
        $data = $this->model;

        if ($this->useNestedOnList) {
            $data = $data->nested();
        }

        $data = $data->filter($this->requestMod()['filter_by'], $this->requestMod()['q'])
            ->where('invoice_id', $invId)
            ->orderBy(
                $this->requestMod()['sort_column'],
                $this->requestMod()['sort_order'])
            ->paginate($this->request->input("per_page"));

        if (!empty($data)) {
            Log::info($this->configName . " fetched");
            return $this->json(Response::HTTP_OK, $this->configName . " fetched.", $data);
        }

        Log::error($this->configName . " Not Found");
        return $this->json(Response::HTTP_BAD_REQUEST, $this->configName . " Not Found", $data);
    }

    public function create($soId = null, $invId = null)
    {
        try {
            return $this->json(Response::HTTP_OK, "get create payment resource",
                $this->_resource($soId, $invId));
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function edit($soId = null, $id = null, $invId = null)
    {
        try {
            if (!empty($invId) && !empty($id)) {
                Payment::inst()->getByIdAndInvoiceId($invId, $id)->firstOrFail();
                $resource = $this->_resource($soId, $invId);
                $resource['payment'] = Payment::inst()->getByIdAndInvoiceId($invId, $id)->firstOrFail();

                return $this->json(Response::HTTP_OK, "get payment in invoice", $resource);
            }
            return $this->json(Response::HTTP_BAD_REQUEST, "param id not found");
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function getByInvoiceId($invId = null)
    {
        try {
            if (!empty($invId)) {
                $payment = Payment::inst()->getByInvoiceId($invId)->get();
                return $this->json(Response::HTTP_OK, "get payments in invoice", $payment);
            }
            return $this->json(Response::HTTP_BAD_REQUEST, "param id not found");
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function getByIdAndInvoiceId($invId, $id)
    {
        try {
            if (!empty($invId) && !empty($id)) {
                $payment = Payment::inst()->getByIdAndInvoiceId($invId, $id)->firstOrFail();
                return $this->json(Response::HTTP_CREATED, "get payment in invoice", $payment);
            }
            return $this->json(Response::HTTP_BAD_REQUEST, "param id not found");
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    private function getExtractedPaymentSetting()
    {
        $paySetting = Setting::findByKeyInOrg("web.payments");
        $paySettingExtracted = json_decode($paySetting->value);

        if (empty($paySettingExtracted)) {
            throw AppException::inst(
                $this->configName .
                ' setting not found. please fill payment setting.',
                Response::HTTP_BAD_REQUEST
            );
        }
        return $paySettingExtracted;
    }

    /**
     * @param $req
     * @return null
     * @throws AppException
     */
    private function getPayment($req)
    {
        $paySettingExtracted = $this->getExtractedPaymentSetting();

        $payment = null;
        foreach ($paySettingExtracted as $key => $value) {
            if ($value->mode_id == (int)$req['payment_mode_id']) {
                $payment = $value;
            }
        }

        if (empty($payment)) {
            throw AppException::inst(
                $this->configName . " mode not found in setting.",
                Response::HTTP_BAD_REQUEST
            );
        }
        return $payment;
    }

    private function getPaymentDetail($payment, $req)
    {
        $paymentDetail = null;
        if (isset($payment->details)) {
            foreach ($payment->details as $key => $value) {
                if ($value->account_id == $req['payment_account_id']) {
                    $paymentDetail = $value;
                }
            }
        }
        return $paymentDetail;
    }

    private function completeStorePaymentRequest(&$req, $inv, $payment, $paymentDetail)
    {
        //TODO(jee): bypass number validation
        /** complete column*/
        $req['action'] = 'store';
        $req['invoice_id'] = $inv->invoice_id;
        $req['payment_mode_id'] = $payment->mode_id;
        $req['payment_mode_name'] = $payment->mode_name;
        $req['payment_account_holder'] = empty($paymentDetail) ? null :
            (string)$paymentDetail->account_holder;
        $req['payment_account_number'] = empty($paymentDetail) ? null :
            (string)"$paymentDetail->account_number";
        $req['payment_account_name'] = empty($paymentDetail) ? null :
            (string)$paymentDetail->account_name;
    }

    private function checkPartialPayment($req, $inv, &$receiptStatus)
    {
        if ($req['amount'] < $inv->balance_due) {
            $receiptStatus .= ', Partial payment is applied';
            return true;
        }
        return false;
    }

    /**
     * @param $req
     * @param $inv
     * @throws AppException
     */
    private function validateInvoiceStatus($req, $inv)
    {
        if ($req['amount'] > $inv->balance_due) {
            throw AppException::inst(
                "Payment amount cannot exceed balance due.",
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        #harus di cek total payment semua
        $model = $this->getModel();

        $allPayment = $model->getByInvoiceId($inv->invoice_id);

        if ($allPayment->count() > 0 && $inv->invoice_status == Invoice::PAID) {
            throw AppException::inst(
                "Your invoice is already paid.",
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    private function getNextPaymentNumber($commit = false)
    {
        $this->paymentService->setup(false);
        $nextId = $this->counterService->getNumbering(Payment::URI, $commit);
        return $nextId;
    }

    public function store(Request $request, $soId = null, $invId = null)
    {
        try {
            DB::beginTransaction();
            $req = $request->input();

            if (empty($invId)) {
                return $this->json(Response::HTTP_BAD_REQUEST,
                    "param invoice id not found");
            }

            $payment = $this->getPayment($req);
            $paymentDetail = $this->getPaymentDetail($payment, $req);

            $inv = $this->invoiceService->getByIdAndSalesOrderId(
                $soId, $invId)->firstOrFail();

            $this->completeStorePaymentRequest($req, $inv, $payment,
                $paymentDetail);

            $receiptStatus = '';

            $isPartial = $this->checkPartialPayment($req, $inv, $receiptStatus);
            $this->validateInvoiceStatus($req, $inv);

            $model = $this->getModel();
            $req['payment_number'] = $this->getNextPaymentNumber(true);
            $data = $model->populate($req);

            if (!$data->save()) {
                $errMsg = "Failed to save " . $this->configName;
                Log::error($errMsg);
                DB::rollback();
                throw AppException::inst(
                    "$errMsg",
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $data->errors);
            }

            if ((boolean)$req['send_receipt']) {
                if ($this->invoiceService->sendPDFEmail($inv)) {

                    if (empty($inv->invoice_email)) {
                        throw AppException::inst(
                            'invoice email is empty, we can not send the email',
                            Response::HTTP_INTERNAL_SERVER_ERROR);
                    }

                    $receiptStatus .= ', receipt sent.';
                } else
                    $receiptStatus .= ', sending receipt failed.';
            }

            $successMsg = $this->configName . " done";
            $message = "$successMsg $receiptStatus";
            Log::info($message);

            $nextStatus = $isPartial ? Invoice::PARTIALLY_PAID : Invoice::PAID;
            $this->invoiceService->setStatus($invId, $nextStatus);
            DB::commit();

            switch ($nextStatus) {
                case 'PARTIALLY_PAID':
                    $message = trans('messages.invoice_partially_paid');
                    break;
                case 'PAID':
                    $message = trans('messages.invoice_paid');
                    break;
            }

            return $this->json(Response::HTTP_CREATED, $message, $data);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->jsonExceptions($e);
        }
    }

    public function update($id = null, Request $request, $invId = null)
    {
        try {
            $req = $request->input();
            $model = $this->getModel();
            $payment = null;

            $paySetting = Setting::findByKeyInOrg("web.payments");
            $paySettingExtracted = json_decode($paySetting->value);

            if (isset($paySettingExtracted)) {
                foreach ($paySettingExtracted as $key => $value) {
                    if ($value->mode_id == (int)$req['payment_mode_id']) {
                        $payment = $value;
                    }
                }
            }

            if (empty($payment))
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    $this->configName . " payment mode not found.");

            $paymentDetail = null;
            if (!empty($payment->details)) {
                foreach ($payment->details as $key => $value) {
                    if ($value->account_id == $req['payment_account_id']) {
                        $paymentDetail = $value;
                    }
                }
            }

            $data = $model
                ->getByIdAndInvoiceId($invId, $id)
                ->firstOrFail();

            $req['action'] = 'update';
            $req['invoice_id'] = $invId;
            $req['payment_mode_id'] = $payment->mode_id;
            $req['payment_mode_name'] = $payment->mode_name;
            $req['payment_account_holder'] = $paymentDetail ? $paymentDetail->account_holder : null;
            $req['payment_account_number'] = $paymentDetail ? $paymentDetail->account_number : null;
            $req['payment_account_name'] = $paymentDetail ? $paymentDetail->account_name : null;

            if (!$data->update($req)) {
                Log::error($this->configName . " with " . $id . " hasn't been updated");
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    $this->configName . " hasn't been updated",
                    $data->errors
                );
            }

            $nextStatus = Invoice::getByIdRef($invId)
                ->first()
                ->balance_due > 0
                ? Invoice::PARTIALLY_PAID
                : Invoice::PAID;

            $this->invoiceService->setStatus($invId, $nextStatus);

            Log::info($this->configName . " with " . $id . " has been updated");
            return $this->json(
                Response::HTTP_CREATED,
                trans('messages.payment_updated'),
                $data);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    //TODO (jee) : masih perlu handle gak bs hapus kalo sudah ada shipme
    public function destroy(Request $request, $invId = null)
    {
        try {
            $input = $request->get('ids');

            if (empty($input)) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "param id not found");
            }

            $ids = explode(',', preg_replace('/\s+/', '', $input));

            $delDataList = array_map(function ($id) use ($invId) {
                $data = $this->model
                    ->getByIdAndInvoiceId($invId, $id)
                    ->first();

                if (!empty($data)) {
                    if ($data->delete()) {

                        Invoice::inst()->setStatus($invId, Invoice::SENT);

                        Log::info($this->configName . " with id " . $id . " successfully deleted");

                        return [
                            "id" => $id,
                            'message' => $this->configName . " with id " . $id . " successfully deleted"
                        ];
                    }

                    Log::error($this->configName . " with id " . $id . "cannot be deleted");
                    return [
                        "id" => -1,
                        "message" => $data['errors']
                    ];
                }
                Log::error($this->configName . " with id " . $id . " doesn't exist");
                return [
                    "id" => -1,
                    "message" => $this->configName . " id " . $id . " in this Organisation doesn't exist"];

            }, $ids);

            $successMsg = trans('messages.payment_deleted');

            if (in_array(-1, array_column($delDataList, 'id'))) {
                throw AppException::flash(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $this->configName . " doesn't exist");
            }

            return $this->json(
                Response::HTTP_OK,
                $successMsg,
                $delDataList);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }


}
