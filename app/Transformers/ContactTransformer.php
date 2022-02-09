<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Models\Contact;
use App\Transformers\Base\Transformer;
use Illuminate\Support\Facades\Log;

class ContactTransformer extends Transformer
{

    const SORT_FIELDS = [
        'contact_id',
        'first_name',
        'last_name',
        'display_name',
        'email',
        'phone',
        'mobile',
        'company_title',
        'company_name',
        'billing_address',
        'billing_zip',
        'billing_fax',
        'shipping_address',
        'shipping_zip',
        'shipping_fax',
        'is_customer',
        'is_vendor',
        'is_reseller',
        'is_dropshipper',
        'billing_country_detail',
        'billing_province_detail',
        'billing_district_detail',
        'billing_region_detail',
        'shipping_country_detail',
        'shipping_province_detail',
        'shipping_district_detail',
        'shipping_region_detail',
    ];

    const SIMPLE_FIELDS = [
        'contact_id',
//        'currency_id',
        'first_name',
        'last_name',
        'display_name',
        'email',
        'phone',
        'mobile',
//        'website',
//        'company_title',
        'company_name',
//        'display_code',
        'billing_address',
        'billing_zip',
        'billing_fax',
        'shipping_address',
        'shipping_zip',
        'shipping_fax',
//        'notes',
//        'contact_status',
        'is_customer',
        'is_vendor',
        'is_reseller',
        'is_dropshipper',
        'billing_country_detail',
        'billing_province_detail',
        'billing_district_detail',
        'billing_region_detail',
        'shipping_country_detail',
        'shipping_province_detail',
        'shipping_district_detail',
        'shipping_region_detail',
    ];

    protected $availableIncludes = [
        'asset_salutation',
        'asset_payment_term'
    ];

    protected $defaultIncludes = [

    ];

    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return $this->filterTransform([
            'contact_id' => $model->contact_id,
            'organization_id' => $model->organization_id,
            'salutation_id' => $model->salutation_id,
            'currency_id' => $model->currency_id,
            'payment_term_id' => $model->payment_term_id,
            'first_name' => $model->first_name,
            'last_name' => $model->last_name,
            'display_name' => $model->display_name,
            'email' => $model->email,
            'phone' => $model->phone,
            'mobile' => $model->mobile,
            'website' => $model->website,
            'company_title' => $model->company_title,
            'company_name' => $model->company_name,
            'display_code' => $model->display_code,
            'billing_address' => $model->billing_address,
            'billing_region' => $model->billing_region,
            'billing_district' => $model->billing_district,
            'billing_province' => $model->billing_province,
            'billing_country' => $model->billing_country,
            'billing_zip' => $model->billing_zip,
            'billing_fax' => $model->billing_fax,
            'shipping_address' => $model->shipping_address,
            'shipping_region' => $model->shipping_region,
            'shipping_district' => $model->shipping_district,
            'shipping_province' => $model->shipping_province,
            'shipping_country' => $model->shipping_country,
            'shipping_zip' => $model->shipping_zip,
            'shipping_fax' => $model->shipping_fax,
            'notes' => $model->notes,
            'contact_status' => $model->contact_status,
            'is_customer' => $model->is_customer,
            'is_vendor' => $model->is_vendor,
            'is_reseller' => $model->is_reseller,
            'is_dropshipper' => $model->is_dropshipper,
            'billing_country_detail' => $model->billing_country_detail ?? [],
            'billing_province_detail' => $model->billing_province_detail ?? [],
            'billing_district_detail' => $model->billing_district_detail ?? [],
            'billing_region_detail' => $model->billing_region_detail ?? [],
            'shipping_country_detail' => $model->shipping_country_detail ?? [],
            'shipping_province_detail' => $model->shipping_province_detail ?? [],
            'shipping_district_detail' => $model->shipping_district_detail ?? [],
            'shipping_region_detail' => $model->shipping_region_detail ?? [],
        ]);
    }

    public function includeAssetSalutation($contact)
    {
        $salutation = $contact->assetSalutation;

        if (!is_null($salutation))
            return $this->item(
                $salutation,
                AssetSalutationTransformer::inst()
                    ->showFields(
                        $this->includeFields['asset_salutation'] ?? []
                    ));

        return $this->null();
    }

    public function includeAssetPaymentTerm($contact)
    {
        $paymentTerm = $contact->assetPaymentTerm;
        if (!is_null($paymentTerm))
            return $this->item(
                $paymentTerm,
                AssetPaymentTermTransformer::inst()
                    ->showFields(
                        $this->includeFields['asset_payment_term'] ?? []
                    ));

        return $this->null();
    }
}