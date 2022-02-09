<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\RestFulControl;
use App\Models\AuthToken;
use App\Models\Setting;
use App\Services\Gateway\Base\BaseServiceContract;
use Exception;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class SettingController extends BaseController
{
    use RestFulControl;

    /**
     * SettingController constructor.
     * @param Request $request
     * @param BaseServiceContract $service
     * @internal param BaseServiceContract $baseServiceContract
     */
    public function __construct(Request $request, BaseServiceContract $service)
    {
        parent::__construct($modelName = Setting::inst(), $request, $useAuth = true, $service);
    }

    //TODO (jee) : ini perlu data default

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function init()
    {
        try {
            $settings['global.timezone.timezone_id'] = 1;
            $settings['global.unit.weight'] = 'gr';
            $settings['global.currency.currency_id'] = null;
            $settings['global.language.language_id'] = null;
            $settings['web.checkout.allow_out_of_stock_order'] = true;
            $settings['web.checkout.order_reserved_hours'] = 3;
            $settings['web.checkout.refund_policy'] = 'refund_policy ';
            $settings['web.checkout.privacy_policy'] = 'privacy_policy ';
            $settings['web.checkout.terms_of_service'] = 'terms_of_service ';
            $settings['web.checkout.refund_policy.sample'] = 'refund_policy.sample ';
            $settings['web.checkout.privacy_policy.sample'] = 'privacy_policy.sample ';
            $settings['web.checkout.terms_of_service.sample'] = 'terms_of_service.sample ';
            $settings['web.shipping.from'] = 'shipping from ';
            $settings['web.shipping.address'] = 'shipping address ';
            $settings['web.shipping.region_id'] = null;
            $settings['web.shipping.district_id'] = null;
            $settings['web.shipping.province_id'] = null;
            $settings['web.shipping.zip_code'] = null;
            $settings['web.shipping.country_id'] = null;
            $settings['web.shipping.phone_number'] = '';
            $settings['web.shipping.courier_ids'] = '[]';
            $settings['web.item.price.tax_included'] = false;
            $settings['web.payments.bank_transfer.bank_accounts'] = '[]';

            foreach ($settings as $key => $value) {
                $setting = Setting::inst();
                $setting->key = $key;
                $setting->value = $value;
                $setting->organization_id = AuthToken::info()->organizationId;
                $setting->save();
                if ($setting["errors"]) {
                    throw AppException::flash(
                        Response::HTTP_BAD_REQUEST,
                        "error create setting init",
                        $setting);
                }
            }

            return $this->json(
                Response::HTTP_CREATED,
                "init setting created successfully",
                $settings);
        } catch (Exception $e) {
            throw $e;
        }

    }

    public function storeDetail(Request $request)
    {
        try {

            $req = $request->input('settings');

            $validator = Validator::make(convertDotToArray($req), [
                'global.timezone.timezone_id' => 'nullable|integer',
                'global.unit.weight' => 'nullable|string|in:gr,kg',
                'global.currency.currency_id' => 'nullable|integer',
                'global.language.language_id' => 'nullable|integer',
                'web.item.price.tax_included' => 'boolean'
            ]);

            if ($validator->fails()) {
                throw AppException::inst(
                    "Error validation",
                    Response::HTTP_BAD_REQUEST,
                    $validator->errors()->all());
            }

            return $this->json(
                Response::HTTP_CREATED,
                "Store detail updated",
                Setting::store($req));

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function setCheckout(Request $request)
    {
        try {
            $req = $request->input('settings');

            $validator = Validator::make(convertDotToArray($req), [
                'web.checkout.allow_out_of_stock_order' => 'boolean',
                'web.checkout.order_reserved_hours' => 'nullable|integer',
                'web.checkout.refund_policy' => 'nullable|string',
                'web.checkout.privacy_policy' => 'nullable|string',
                'web.checkout.terms_of_service' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "Error validation",
                    $validator->errors()->all());
            }

            return $this->json(
                Response::HTTP_CREATED,
                "success update checkout setting",
                Setting::store($req));

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function setShipping(Request $request)
    {
        try {
            $req = $request->input('settings');

            $validator = Validator::make(convertDotToArray($req), [
                'web.shipping.carrier_ids.*' => 'nullable|integer',
                'web.shipping.from' => 'nullable|nullable|string',
                'web.shipping.address' => 'nullable|string',
                'web.shipping.region_id' => 'nullable|integer',
                'web.shipping.district_id' => 'nullable|integer',
                'web.shipping.province_id' => 'nullable|integer',
                'web.shipping.zip_code' => 'nullable|string',
                'web.shipping.country_id' => 'nullable|integer',
                'web.shipping.phone_number' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "Error validation",
                    (array)$validator->errors()->all());
            }


            return $this->json(
                Response::HTTP_CREATED,
                "Shipping setting updated.",
                Setting::store($req));

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }

    }

    public function setTaxes(Request $request)
    {
        try {
            $req = $request->input('settings');

            $validator = Validator::make(convertDotToArray($req), [
                'web.item.price.tax_included' => 'boolean',
            ]);

            if ($validator->fails()) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "Error validation",
                    (array)$validator->errors()->all());
            }

            return $this->json(
                Response::HTTP_CREATED,
                "success update tax setting",
                Setting::store($req));

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }

    }

    /** step
     * 1. check mode_id exist in payment_methods
     * 2. get setting payment
     * 3. check payment is exist in payment_setting
     * 4. if not exist populate then push
     * 5. set new payment value
     * 6. save
     *
     * @param Request $request
     * @return mixed
     */
    public function addPaymentMethod(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'mode_id' => 'integer',
            ]);

            $modeId = $request->input('mode_id');

            if ($validator->fails()) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "Error validation",
                    (array)$validator->errors()->all());
            }

            $promises = [
                'paymentMethod' => $this
                    ->service
                    ->getAsync('/payment_methods/' . $modeId, ['id' => $modeId])
            ];

            $res = Promise\unwrap($promises);

            $paymentMethod = json_decode($res['paymentMethod']->getBody())->data ?? [];

            if (!isset($paymentMethod)) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "payment method doesn't exist.");

            }

            $paySetting = Setting::findByKeyInOrg("web.payments");

            if (empty($paySetting->value))
                $paySetting->value = '[]';

            $paySettingExtracted = json_decode($paySetting->value);

            foreach ($paySettingExtracted as $key => $value) {
                if (isset($value->mode_id) && $value->mode_id == $paymentMethod->id) {
                    return $this->json(Response::HTTP_BAD_REQUEST, "payment exist.");
                }
            }

            $pop = (object)array(
                'mode_id' => $paymentMethod->id,
                'mode_name' => $paymentMethod->name,
                'details' => []
            );

            array_push($paySettingExtracted, $pop);

            $paySetting->value = '';
            $paySetting->value = json_encode(
                $paySettingExtracted,
                JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);

            if (!$paySetting->save()) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "save payment failed.",
                    $paySetting);
            }

            return $this->json(
                Response::HTTP_CREATED,
                trans('messages.payment_method_saved'),
                Setting::reformatKeyOutput("web.payments"));


        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        } catch (\Throwable $e) {
            return $this->jsonExceptions($e);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function destroyPaymentMethod()
    {
        try {
            $id = $this->request->get('id');

            $paySetting = Setting::findByKeyInOrg("web.payments");

            $paySettingExtracted = json_decode($paySetting->value);

            if (empty($paySettingExtracted))
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    'No any payment exist.');

            $i = 0;
            foreach ($paySettingExtracted as $k => $v) {
                if ($v->mode_id == $id) {
                    if (!empty($v->details)) {
                        throw AppException::flash(
                            Response::HTTP_BAD_REQUEST,
                            'detail is not empty');
                    }

                    unset($paySettingExtracted[$k]);
                    $i++;
                }
            }

            if ($i == 0)
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    'data not found.');

            $paySettingExtracted = array_values($paySettingExtracted); // 'reindex' array

            $paySetting->value = json_encode(
                $paySettingExtracted,
                JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);

            if (!$paySetting->save()) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "remove payment failed.",
                    $paySetting);
            }

            return $this->json(
                Response::HTTP_OK,
                trans('messages.payment_methode_removed'),
                Setting::reformatKeyOutput("web.payments"));

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function addPaymentMethodDetail($id, Request $request)
    {
        try {
            $req = $request->input();

            $validator = Validator::make($request->all(), [
                'account_holder' => 'required|string',
                'account_number' => 'required|string',
                'account_name' => 'required|string'
//                'bank_logo' => 'required|string'
            ]);

            if ($validator->fails()) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "Error validation",
                    (array)$validator->errors()->all());
            }

            $paySetting = Setting::findByKeyInOrg("web.payments");

            $paySettingExtracted = json_decode($paySetting->value);

            if (!is_array($paySettingExtracted) && empty($paySettingExtracted)) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "empty payment detail, please insert payment first.");
            }

            /** populate */
            $pop = array_map(function ($o) use ($id, $req) {

                if ($o->mode_id == $id) {
                    $id = str_slug(strtolower($req['account_name'] . '-' . $req['account_number']));

                    if (empty($o->details)) {
                        $newDetails = (object)array(
                            'account_id' => $id,
                            'account_holder' => (string)$req['account_holder'],
                            'account_number' => (string)$req['account_number'],
                            'account_name' => (string)$req['account_name']
//                            'bank_logo' => (string)$req['bank_logo']
                        );
                    } else {
                        $newDetails = array();
                        foreach ($o->details as $k => $v) {
                            if ($v->account_id === $id) {
                                $newDetails = (object)['errors' => ['code' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => "account exist"]];
                            } else {

                                $newDetails = (object)array(
                                    'account_id' => $id,
                                    'account_holder' => (string)$req['account_holder'],
                                    'account_number' => (string)$req['account_number'],
                                    'account_name' => (string)$req['account_name']
//                                    'bank_logo' => (string)$req['bank_logo']
                                );
                            }
                        }

                    }

                    array_push($o->details, $newDetails);

                }
                return $o;
            }, $paySettingExtracted);

            foreach ($pop as $k => $v) {
                foreach ($v->details as $key => $val) {
                    if (isset($val->errors))
                        throw AppException::flash(
                            Response::HTTP_BAD_REQUEST,
                            $val->errors['message']);
                }
            }

            $paySetting->value = '';
            $paySetting->value = json_encode($pop, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);

            if (!$paySetting->save()) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "save payment detail failed.",
                    $paySetting);
            }

            return $this->json(
                Response::HTTP_CREATED,
                trans('messages.payment_account_added'),
                Setting::reformatKeyOutput("web.payments"));


        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function destroyPaymentMethodDetail()
    {
        try {

            $id = $this->request->get('id');

            $paySetting = Setting::findByKeyInOrg("web.payments");

            $paySettingExtracted = json_decode($paySetting->value);

            if (empty($paySettingExtracted))
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "payment method does not exist.");

            $i = 0;
            foreach ($paySettingExtracted as $k => $v) {
                foreach ($v->details as $key => $val) {
                    if ($val->account_id == $id) {
                        unset($v->details[$key]); // remove item at index 0
                        $i++;
                    }
                }
                $v->details = array_values($v->details); // 'reindex' array
            }

            if ($i == 0) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "data not found.");
            }

            $paySetting->value = json_encode($paySettingExtracted, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);

            if (!$paySetting->save()) {
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    "remove payment detail failed.",
                    $paySetting);
            }

            return $this->json(
                Response::HTTP_OK,
                trans('messages.payment_account_removed'),
                Setting::reformatKeyOutput("web.payments"));

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \Throwable
     */
    public function edit()
    {
        try {

            //setup default
            Setting::setupDefaultData();

            $settings = Setting::reFormatOutput();

            if (!$settings)
                throw AppException::inst(
                    'Setting not found, on this organization',
                    Response::HTTP_INTERNAL_SERVER_ERROR);

            $promise = [
                'area' => $this->service->getAsync('/countries/areas',
                    [
                        'countryId' => $settings['web.shipping.country_id'],
                        'provinceId' => $settings['web.shipping.province_id'],
                        'districtId' => $settings['web.shipping.district_id'],
                        'regionId' => $settings['web.shipping.region_id'],
                    ]
                ),
                'weightUnit' => $this->service->getAsync('/weight_units/code/' . $settings['global.unit.weight'],
                    [
                        'code' => $settings['global.unit.weight']
                    ]
                ),
            ];

            $res = Promise\unwrap($promise);

            $area = json_decode($res['area']->getBody())->data ?? [];

            $weightUnit = json_decode($res['weightUnit']->getBody())->data ?? [];

            $pop = [
                'settings' => $settings,
            ];

            $pop['settings']['web.shipping.country'] = $area->country ?? null;
            $pop['settings']['web.shipping.province'] = $area->province ?? null;
            $pop['settings']['web.shipping.district'] = $area->district ?? null;
            $pop['settings']['web.shipping.region'] = $area->region ?? null;
            $pop['settings']['global.unit.weight_detail'] = $weightUnit ?? [];

            return $this->json(
                Response::HTTP_OK,
                trans('messages.settings_fetched'),
                $pop);

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
