<?php

namespace App\Models;

use App\Exceptions\AppException;
use App\Models\Base\BaseModel;
use App\Models\Contract\RestModelContract;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Contact extends MasterModel
{

    const STATUS_ALL = -9;
    const STATUS_CUSTOMER = 3;
    const STATUS_DROPSHIPPER = 4;
    const STATUS_VENDOR = 5;
    const STATUS_RESELLER = 6;

    const CONTACT_TYPES = [
        self::STATUS_CUSTOMER => 'Customer',
        self::STATUS_DROPSHIPPER => 'Dropshipper',
        self::STATUS_VENDOR => 'Vendor',
        self::STATUS_RESELLER => 'Reseller',
    ];

    protected $table = 'contacts';

    protected $primaryKey = 'contact_id';

    protected $columnStatus = "contact_status";

    protected $columnDefault = array("*");

    protected $columnSimple = array("*");

//    protected $with = [
//        "organization",
//        "asset_salutation",
//        "asset_currency",
//        "asset_payment_term",
//        "billing_country",
//        "billing_province",
//        "billing_district",
//        "billing_region",
//        "shipping_country",
//        "shipping_province",
//        "shipping_district",
//        "shipping_region"
//    ];


    protected $casts = [
        'organization_id' => 'integer',
        'salutation_id' => 'integer',
        'currency_id' => 'integer',
        'payment_term_id' => 'integer',
        'first_name' => 'string',
        'last_name' => 'string',
        'display_name' => 'string',
        'email' => 'email',
        'phone' => 'numeric',
        'mobile' => 'numeric',
        'website' => 'string',
        'company_title' => 'string',
        'company_name' => 'string',
        'display_code' => 'integer',
        'billing_address' => 'string',
        'billing_region' => 'integer',
        'billing_district' => 'integer',
        'billing_province' => 'integer',
        'billing_country' => 'integer',
        'billing_zip' => 'alpha_num',
        'billing_fax' => 'string',
        'shipping_address' => 'string',
        'shipping_region' => 'integer',
        'shipping_district' => 'integer',
        'shipping_province' => 'integer',
        'shipping_country' => 'integer',
        'shipping_zip' => 'alpha_num',
        'shipping_fax' => 'string',
        'notes' => 'string',
        'contact_status' => 'boolean',
        'is_customer' => 'boolean',
        'is_vendor' => 'boolean',
        'is_dropshipper' => 'boolean',
        'is_reseller' => 'boolean',
    ];

    protected $appends = [
        'contact_type',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->nestedBelongConfigs = array(
            "assetSalutation" => AssetSalutation::inst()->getColumnSimple(),
            "assetPaymentTerm" => AssetPaymentTerm::inst()->getColumnSimple(),
        );

//        $this->nestedHasManyConfigs = array(

//        );

    }

    /**use phone library*/
//    public function getMobileAttribute($v)
//    {
//        if ($v)
//            return PhoneNumber::make($v, 'ID')->formatNational();
//
//        return null;
//    }
//
//    public function getPhoneAttribute($v)
//    {
//        if ($v)
//            return PhoneNumber::make($v, 'ID')->formatNational();
//
//        return null;
//
//    }

    public function getContactTypeAttribute()
    {
        $types = [];

        if ($this->is_customer) {
            $types[] = self::CONTACT_TYPES[self::STATUS_CUSTOMER];
        }
        if ($this->is_vendor) {
            $types[] = self::CONTACT_TYPES[self::STATUS_VENDOR];
        }
        if ($this->is_reseller) {
            $types[] = self::CONTACT_TYPES[self::STATUS_RESELLER];
        }
        if ($this->is_dropshipper) {
            $types[] = self::CONTACT_TYPES[self::STATUS_DROPSHIPPER];
        }
        return implode(',', $types);
    }

    public function assetSalutation()
    {
        return $this->belongsTo(AssetSalutation::class, 'salutation_id');
    }

    public function assetPaymentTerm()
    {
        return $this->belongsTo(AssetPaymentTerm::class, 'payment_term_id');
    }

    public function rules($id = null)
    {

        $forUpdate = $id ? ',' . $id . ',contact_id' : '';

        return [
            'organization_id' => 'required|integer',
            'salutation_id' => 'nullable|integer|exists:asset_salutations,salutation_id',
            'currency_id' => 'nullable|integer',
            'payment_term_id' => 'nullable|integer|exists:asset_payment_terms,payment_term_id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'display_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:50|org_unique:contacts,email' . $forUpdate,// add contact_id to bypass unique value on update
            'phone' => 'nullable|string|min:9|max:15|regex:/^\+?[^a-zA-Z]{5,}$/',
            'mobile' => 'nullable|string|min:9|max:15|regex:/^\+?[^a-zA-Z]{5,}$/',
            'website' => 'nullable|string|max:100|regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
            'company_title' => 'nullable|string|max:5',
            'company_name' => 'nullable|string|max:100',
            'display_code' => 'nullable|integer|in:1,2',
            'billing_address' => 'nullable|string|max:255',
            'billing_region' => 'nullable|integer',
            'billing_district' => 'nullable|integer',
            'billing_province' => 'nullable|integer',
            'billing_country' => 'nullable|integer',
            'billing_zip' => 'nullable|string|min:5|max:5|regex:/^\+?[^a-zA-Z]{5,}$/',
            'billing_fax' => 'nullable|string|max:20',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_region' => 'nullable|integer',
            'shipping_district' => 'nullable|integer',
            'shipping_province' => 'nullable|integer',
            'shipping_country' => 'nullable|integer',
            'shipping_zip' => 'nullable|string|min:5|max:5|regex:/^\+?[^a-zA-Z]{5,}$/',
            'shipping_fax' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
            'contact_status' => 'nullable|boolean',
            'is_customer' => 'nullable|boolean',
            'is_vendor' => 'nullable|boolean',
            'is_reseller' => 'nullable|boolean',
            'is_dropshipper' => 'nullable|boolean'
        ];
    }

    /**
     * Check is Contact is exist ?
     * @param $contactId
     * @param $organizationId
     * @return boolean
     */
    public static function isContactExist($contactId, $organizationId)
    {
        $contactExist = Contact::where("contact_id", "=", $contactId)
            ->where("organization_id", "=", $organizationId)->get()->toArray();

        if (empty($contactExist)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Instantiate Contact
     * @return Contact
     */
    public static function inst()
    {
        return new Contact();
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($item) {
            if ($item->hasSalesOrder()) {
                throw new AppException(
                    trans('messages.delete_contact_is_failed'),
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }
        });
    }

    public function hasSalesOrder()
    {
        return $this->salesOrders()->exists();
    }

    protected function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'contact_id');
    }

    public function populate($request = [], BaseModel $model = null)
    {
        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);

        $model->organization_id = AuthToken::info()->organizationId;

        $model->salutation_id = $req->get('salutation_id') ?? null;
        $salutation = AssetSalutation::inst()->getByIdInOrgRef($model->salutation_id)->first();

        $model->first_name = $req->get('first_name');
        $model->last_name = $req->get('last_name');
        $model->display_code = $req->get('display_code');
        $model->company_title = $req->get('company_title');
        $model->company_name = $req->get('company_name');

        switch ($req->get('display_code')) {
            case 1:
                $salutationName = !empty($salutation) ? "$salutation->name " : '';
                $model->display_name = "$salutationName$model->first_name $model->last_name";
                break;
            case 2:
                $model->display_name = $model->company_name;
                break;
            default:
                $model->display_name = $model->company_name;
                break;
        }

        $model->email = strtolower($req->get('email'));

        $model->phone = $req->get('phone') ?? null;
        $model->mobile = $req->get('mobile') ?? null;

        $model->website = $req->get('website');

        $payTermId = function () use ($req) {
            if ($req->get('payment_term_id')) {
                return (int)$req->get('payment_term_id');
            }
            // object default harus ada
            return (int)AssetPaymentTerm::inst()->getPaymentDefault()->first()->payment_term_id;
        };

        $model->currency_id = $req->get('currency_id') ?? Setting::findByKeyInOrg('global.currency.currency_id')->value;

        $model->payment_term_id = intOrNull($payTermId());

        $model->billing_address = $req->get('billing_address');
        $model->billing_region = intOrNull($req->get('billing_region'));
        $model->billing_district = intOrNull($req->get('billing_district'));
        $model->billing_province = intOrNull($req->get('billing_province'));
        $model->billing_country = intOrNull($req->get('billing_country'));
        $model->billing_zip = $req->get('billing_zip');
        $model->billing_fax = (string)$req->get('billing_fax');

        $model->shipping_address = $req->get('shipping_address');
        $model->shipping_region = intOrNull($req->get('shipping_region'));
        $model->shipping_district = intOrNull($req->get('shipping_district'));
        $model->shipping_province = intOrNull($req->get('shipping_province'));
        $model->shipping_country = intOrNull($req->get('shipping_country'));
        $model->shipping_zip = $req->get('shipping_zip');
        $model->shipping_fax = (string)$req->get('shipping_fax');
        $model->notes = $req->get('notes');
        $model->contact_status = ($req->get('contact_status')) ? $req->get('contact_status') : true;
        $model->is_customer = $req->get('is_customer') ?? false;
        $model->is_vendor = $req->get('is_vendor') ?? false;
        $model->is_reseller = $req->get('is_reseller') ?? false;
        $model->is_dropshipper = $req->get('is_dropshipper') ?? false;

        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
//        $data = $q->getInOrgRef();

        $data = DB::table('contacts')->where('organization_id', AuthToken::info()->organizationId);
        switch ($filterBy) {
            case self::STATUS_CUSTOMER:
                $data = $data
                    ->where("is_customer", true);
                break;
            case self::STATUS_VENDOR:
                $data = $data
                    ->where("is_vendor", true);
                break;
            case self::STATUS_DROPSHIPPER:
                $data = $data
                    ->where("is_dropshipper", true);
                break;
            case self::STATUS_RESELLER:
                $data = $data
                    ->where("is_reseller", true);
                break;
            case self::STATUS_ACTIVE:
                $data = $data
                    ->where("contact_status", true);
                break;
            case self::STATUS_INACTIVE:
                $data = $data
                    ->where("contact_status", false);
                break;
        }

        if (!empty($key)) {
            $data = $data
                ->where(function ($query) use ($key) {
                    $query->where("first_name", "ILIKE", "%" . $key . "%")
                        ->orWhere("last_name", "ILIKE", "%" . $key . "%")
                        ->orWhere("display_name", "ILIKE", "%" . $key . "%")
                        ->orWhere("email", "ILIKE", "%" . $key . "%")
                        ->orWhere("phone", "ILIKE", "%" . $key . "%")
                        ->orWhere("website", "ILIKE", "%" . $key . "%")
                        ->orWhere("mobile", "ILIKE", "%" . $key . "%")
                        ->orWhere("company_title", "ILIKE", "%" . $key . "%")
                        ->orWhere("company_name", "ILIKE", "%" . $key . "%");
                }
                );
        }

        return $data;
    }

}
