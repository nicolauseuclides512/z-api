<?php

namespace App\Models;

use App\Domain\Contracts\MySalesChannelContract;
use App\Domain\Contracts\PaymentContract;
use App\Domain\Contracts\SalesOrderContract;
use App\Domain\Contracts\ShipmentContract;
use App\Domain\Contracts\StockAdjustmentContract;
use App\Exceptions\AppException;
use App\Models\Base\BaseModel;
use App\Services\Gateway\Master\Bank\BankServiceContract;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Setting extends MasterModel
{

    protected $table = 'settings';

    protected $primaryKey = 'setting_id';

    /**
     * @param $key
     * @return mixed
     * @throws AppException
     */
    public static function findByKeyInOrg($key)
    {
        $data = self::where('key', $key)
            ->where('organization_id', AuthToken::info()->organizationId)
            ->first();

        if (!$data) {
            throw AppException::inst(
                'Setting key ' . $key . ' was not found',
                Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $data;

    }

    /**
     * @param $req
     * @return array
     * @throws \Exception
     */
    public static function store($req)
    {
        try {
            $settings = [];
            foreach ($req as $k => $v) {

                $data = Setting::findByKeyInOrg($k);
                if (!$data) {
                    throw AppException::inst(
                        'Key not found.',
                        Response::HTTP_BAD_REQUEST);
                }

                $data->value = $v;
                if (!$data->save()) {
                    throw AppException::inst(
                        'save setting failed.',
                        Response::HTTP_INTERNAL_SERVER_ERROR,
                        $data->errors);

                }
                array_push($settings, $data);
            }

            return self::reFormatOutput();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $key
     * @return mixed
     * @throws AppException
     */
    public static function reformatKeyOutput($key)
    {
        $banks = app(BankServiceContract::class);

        $payments = self::findByKeyInOrg($key);

        $data = json_decode($payments['value']);

        if ($key == 'web.payments') {
            foreach ($data as $v) {
                if ($v->mode_id == 1) {
                    $v->details = array_map(function ($o) use ($banks) {
                        $o->logo = $banks->getLogoByName($o->account_name);
                        return $o;
                    }, $v->details);
                }
            }
        }

        return $data['key'] = $data;


    }

    public static function reFormatOutput()
    {

        $banks = app(BankServiceContract::class);

        $data = self::getInOrgRef()->get()->toArray();
        $res = [];


        foreach ($data as $key => $value) {
            if ($value['key'] == 'web.shipping.courier_ids' || preg_match('/web.template.notification.email./', $value['key'])) {
                $res[$value['key']] = json_decode($value['value']);
            } else if ($value['key'] == 'web.payments') {
                $data = json_decode($value['value']);
                foreach ($data as $v) {
                    if ($v->mode_id == 1) {
                        $v->details = array_map(function ($o) use ($banks) {
                            $o->logo = $banks->getLogoByName($o->account_name);
                            return $o;
                        }, $v->details);
                    }
                }

                $res[$value['key']] = $data;
            } else
                $res[$value['key']] = $value['value'];
        }
        return $res;
    }

    public function getKeyAttribute($value)
    {
        return $this->attributes['key'] = $value;
    }

    public static function inst()
    {
        return new self();
    }

    public function populate($request = array(), BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q;

        return $data;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public static function setupDefaultData()
    {
        $orgId = AuthToken::info()->organizationId;

        if (Setting::where('organization_id', $orgId)->count() > 0) {
            Log::info('No need to setup setting data, skip this.');
            return false;
        }

        DB::beginTransaction();

        Log::info('Generate default data.');

        $stockAdjustmentService = app(StockAdjustmentContract::class);
        $salesOrderService = app(SalesOrderContract::class);
        $paymentService = app(PaymentContract::class);
        $shipmentService = app(ShipmentContract::class);
        $mySalesChannel = app(MySalesChannelContract::class);

        try {
            #settings
            $settings['global.timezone.timezone_id'] = 98;
            $settings['global.unit.weight'] = 'gr';
            $settings['global.currency.currency_id'] = 1;
            $settings['global.language.language_id'] = 103;
            $settings['web.checkout.allow_out_of_stock_order'] = true;
            $settings['web.checkout.order_reserved_hours'] = 3;
            $settings['web.checkout.refund_policy'] = '';
            $settings['web.checkout.privacy_policy'] = '';
            $settings['web.checkout.terms_of_service'] = '';
            $settings['web.checkout.refund_policy.sample'] = '';
            $settings['web.checkout.privacy_policy.sample'] = '';
            $settings['web.checkout.terms_of_service.sample'] = '';
            $settings['web.shipping.from'] = '';
            $settings['web.shipping.address'] = '';
            $settings['web.shipping.country_id'] = 236;
            $settings['web.shipping.province_id'] = null;
            $settings['web.shipping.district_id'] = null;
            $settings['web.shipping.region_id'] = null;
            $settings['web.shipping.zip_code'] = null;
            $settings['web.shipping.phone_number'] = null;
            $settings['web.shipping.carrier_ids'] = null;
            $settings['web.item.price.tax_included'] = true;
            $settings['web.template.notification.email.forgot_password'] = json_encode(Config::get('templates.notification.email.forgot_password'));
            $settings['web.template.notification.email.payment_receipt'] = json_encode(Config::get('templates.notification.email.payment_receipt'));
            $settings['web.template.notification.email.invoice'] = json_encode(Config::get('templates.notification.email.invoice'));
            $settings['web.template.notification.email.resend_verification'] = json_encode(Config::get('templates.notification.email.payment_receipt'));
            $settings['web.template.term_and_conditions'] = json_encode(Config::get('template.term_and_conditions'));

            $settings['web.payments'] = json_encode(json_decode('[{"mode_id":1, "mode_name":"Bank Transfer", "details":[]},{"mode_id":2, "mode_name":"Cash", "details":[]}]'));

            foreach ($settings as $key => $value) {
                $setting = Setting::inst();
                $setting->key = $key;
                $setting->value = $value;
                $setting->organization_id = $orgId;
                if (!$setting->save()) {
                    Log::error('setup setting failed');
                    DB::rollback();
                    throw AppException::inst(
                        'setup setting failed',
                        Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            #asset Uom
            $uomData = ["pcs", "box", "meter", "centimeter", "kilogram", "gram", "set"];
            array_map(function ($x) use ($orgId) {
                $uom = AssetUom::inst();
                $uom->organization_id = (int)$orgId;
                $uom->name = $x;
                $uom->description = str_random(20);
                $uom->uom_status = 1;
                $uom->is_default = $uom->name == 'pcs' ? true : false;

                if (!$uom->save()) {
                    Log::error('setup uom failed');
                    DB::rollback();
                    throw AppException::inst('setup uom failed', Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }, $uomData);

            #asset salutation
            $salutationData = ["Mr", "Mrs", "Miss", "Ms"];

            array_map(function ($x) use ($orgId) {
                $salutation = AssetSalutation::inst();
                $salutation->organization_id = (int)$orgId;
                $salutation->name = $x;
                $salutation->salutation_status = 1;
                if (!$salutation->save()) {
                    Log::error('setup salutation failed');
                    DB::rollback();
                    throw AppException::inst(
                        'setup salutation failed',
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
            }, $salutationData);

            #asset tax
            $tax = AssetTax::inst();
            $tax->organization_id = (int)$orgId;
            $tax->name = "PPN";
            $tax->percent = 10;
            $tax->tax_status = 1;
            $tax->save();

            #asset paymentTerm
            $paymentTermData = [
                "Net 15", "Net 30", "Net 45", "Net 60", "Due End of the Month", "Due End of Next Month", "Due on Receipt"
            ];

            array_map(function ($x) use ($orgId) {
                $payment_term = AssetPaymentTerm::inst();
                $payment_term->organization_id = (int)$orgId;
                $payment_term->name = $x;
                $payment_term->description = "description of " . $x;
                $payment_term->day = 10;
                $payment_term->payment_term_status = 1;
                $payment_term->is_default = true;
                if (!$payment_term->save()) {
                    Log::error('setup payment term failed');
                    DB::rollback();
                    throw AppException::inst(
                        'setup payment term failed',
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
            }, $paymentTermData);

            #asset category

            #asset salesPerson

            #asset attribute
            $assetAttributeData = [];

            array_map(function ($x) use ($orgId) {
                $attr = AssetAttribute::inst();
                $attr->organization_id = (int)$orgId;
                $attr->name = $x;
                if (!$attr->save()) {
                    Log::error('setup attribute failed');
                    DB::rollback();
                    throw AppException::inst(
                        'setup attribute failed',
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
            }, $assetAttributeData);

            #asset account

            #set template

            # domain services
            $stockAdjustmentService->setup();
            $salesOrderService->setup();
            $paymentService->setup();
            $shipmentService->setup();
            $mySalesChannel->setup();

            DB::commit();
            return false;
        } catch (\Exception $e) {
            DB::rollback();
            throw  $e;
        }
    }
}
