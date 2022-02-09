<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Models\SalesOrder;
use App\Transformers\Base\Transformer;

class SalesOrderTransformer extends Transformer
{

    const SORT_FIELDS = [
        'sales_order_id',
        'sales_order_number',
        'sales_order_date',
        'invoice_date',
        'shipment_date',
        'sales_order_status',
        'total',
        'invoice_status',
        'shipment_status',
        'is_overdue',
        'due_date',
        'sub_total',
        'tax',
        'tax_included',
        'shipping_charge',
        'shipments',
        'payments'
    ];

    const SIMPLE_FIELDS = [
        'sales_order_id',
        'contact_id',
        'sales_order_number',
        'reference_number',
        'sales_order_date',
        'due_date',
        'invoice_date',
        'shipment_date',
        'discount_contact_id',
        'discount_amount_type',
        'discount_amount_value',
        'shipping_weight',
        'shipping_weight_unit',
        'shipping_rate',
        'adjustment_name',
        'adjustment_value',
        'term_and_condition',
        'customer_notes',
        'internal_notes',
        'term_date',
        'sales_order_status',
        'invoice_email',
        'billing_address',
        'billing_region',
        'billing_district',
        'billing_province',
        'billing_country',
        'billing_zip',
        'billing_fax',
        'billing_phone',
        'billing_mobile',
        'shipping_address',
        'shipping_region',
        'shipping_district',
        'shipping_province',
        'shipping_country',
        'shipping_zip',
        'shipping_fax',
        'tax_included',
        'shipping_carrier_name',
        'shipping_carrier_code',
        'shipping_carrier_id',
        'shipping_carrier_service',
        'shipped_status',
        'paid_status',
        'my_sales_channel_id',
        'shipping_from_address',
        'shipping_from_region',
        'shipping_from_district',
        'shipping_from_province',
        'shipping_from_country',
        'shipping_from_zip',
        'shipping_from_fax',
        'shipping_phone',
        'shipping_mobile',
        'is_dropship',
        'total',
        'sub_total',
        'tax',
        'shipping_charge',
        'invoice_status',
        'shipment_status',
        'is_overdue',
        'shipment_id',
        'billing_country_detail',
        'billing_province_detail',
        'billing_district_detail',
        'billing_region_detail',
        'shipping_country_detail',
        'shipping_province_detail',
        'shipping_district_detail',
        'shipping_region_detail',
        'payments',
        'shipments'
    ];

    protected $availableIncludes = [
        'sales_order_details',
        'asset_oum',
        'asset_tax',
        'invoices',
        'contact',
        'my_sales_channel'
    ];

    protected $defaultIncludes = [

    ];

    public static function inst()
    {
        return new self();
    }

    public function transform(SalesOrder $model)
    {
        return $this->filterTransform([
            'sales_order_id' => $model->sales_order_id,
            'organization_id' => $model->organization_id,
            'contact_id' => $model->contact_id,
            'sales_order_number' => $model->sales_order_number,
            'reference_number' => $model->reference_number,
            'sales_order_date' => $model->sales_order_date,
            'due_date' => $model->due_date,
            'invoice_date' => $model->invoice_date,
            'shipment_date' => $model->shipment_date,
            'discount_contact_id' => $model->discount_contact_id,
            'discount_amount_type' => $model->discount_amount_type,
            'discount_amount_value' => (float)$model->discount_amount_value,
            'shipping_weight' => $model->shipping_weight,
            'shipping_weight_unit' => $model->shipping_weight_unit,
            'shipping_rate' => (float)$model->shipping_rate,
            'adjustment_name' => $model->adjustment_name,
            'adjustment_value' => (float)$model->adjustment_value,
            'term_and_condition' => $model->term_and_condition,
            'customer_notes' => $model->customer_notes,
            'internal_notes' => $model->internal_notes,
            'term_date' => $model->term_date,
            'invoice_email' => $model->invoice_email,
            'billing_address' => $model->billing_address,
            'billing_region' => $model->billing_region,
            'billing_district' => $model->billing_district,
            'billing_province' => $model->billing_province,
            'billing_country' => $model->billing_country,
            'billing_zip' => $model->billing_zip,
            'billing_fax' => $model->billing_fax,
            'billing_phone' => $model->billing_phone,
            'billing_mobile' => $model->billing_mobile,
            'shipping_address' => $model->shipping_address,
            'shipping_region' => $model->shipping_region,
            'shipping_district' => $model->shipping_district,
            'shipping_province' => $model->shipping_province,
            'shipping_country' => $model->shipping_country,
            'shipping_zip' => $model->shipping_zip,
            'shipping_fax' => $model->shipping_fax,
            'tax_included' => $model->tax_included,
            'shipping_carrier_name' => $model->shipping_carrier_name,
            'shipping_carrier_code' => $model->shipping_carrier_code,
            'shipping_carrier_id' => $model->shipping_carrier_id,
            'shipping_carrier_service' => $model->shipping_carrier_service,
            'shipped_status' => $model->shipped_status,
            'paid_status' => $model->paid_status,
            'my_sales_channel_id' => $model->my_sales_channel_id,
            'shipping_from_address' => $model->shipping_from_address,
            'shipping_from_region' => $model->shipping_from_region,
            'shipping_from_district' => $model->shipping_from_district,
            'shipping_from_province' => $model->shipping_from_province,
            'shipping_from_country' => $model->shipping_from_country,
            'shipping_from_zip' => $model->shipping_from_zip,
            'shipping_from_fax' => $model->shipping_from_fax,
            'shipping_phone' => $model->shipping_phone,
            'shipping_mobile' => $model->shipping_mobile,
            'is_dropship' => $model->is_dropship,
            'invoice_status' => $model->invoice_status,
            'total' => $model->total,
            'sub_total' => $model->sub_total,
            'tax' => $model->tax,
            'shipping_charge' => $model->shipping_charge,
            'shipment_status' => $model->shipment_status,
            'is_overdue' => $model->is_overdue,
            'shipment_id' => $model->shipment_id,
            'sales_order_status' => $model->sales_order_status,

            'billing_country_detail' => $model->billing_country_detail ?? null,
            'billing_province_detail' => $model->billing_province_detail ?? null,
            'billing_district_detail' => $model->billing_district_detail ?? null,
            'billing_region_detail' => $model->billing_region_detail ?? null,
            'shipping_country_detail' => $model->shipping_country_detail ?? null,
            'shipping_province_detail' => $model->shipping_province_detail ?? null,
            'shipping_district_detail' => $model->shipping_district_detail ?? null,
            'shipping_region_detail' => $model->shipping_region_detail ?? null,

            'shipments' => $model->shipments,
            'payments' => $model->payments,

        ]);
    }

    public function includeInvoices(SalesOrder $salesOrder)
    {
        $invoices = $salesOrder->invoices;

        return $invoices
            ? $this->collection(
                $invoices,
                InvoiceTransformer::inst()
                    ->showFields(
                        $this->includeFields['invoices'] ?? []
                    ))
            : $this->null();
    }

    public function includeContact(SalesOrder $salesOrder)
    {
        $contact = $salesOrder->contact;

        return $contact
            ? $this->item(
                $contact,
                ContactTransformer::inst()
                    ->showFields(
                        $this->includeFields['contact'] ?? []
                    ))
            : $this->null();

    }

    public function includeSalesOrderDetails(SalesOrder $salesOrder)
    {
        $soDetails = $salesOrder->sales_order_details;

        return $soDetails
            ? $this->collection(
                $soDetails,
                SalesOrderDetailTransformer::inst()
                    ->showFields(
                        $this->includeFields['sales_order_details'] ?? []
                    ))
            : $this->null();
    }

    public function includeAssetUom(SalesOrder $salesOrder)
    {
        $uom = $salesOrder->asset_uom;

        return $uom
            ? $this->item(
                $uom,
                AssetUomTransformer::inst()
                    ->showFields(
                        $this->includeFields['asset_uom'] ?? []
                    ))
            : $this->null();
    }

    public function includeAssetTax(SalesOrder $salesOrder)
    {
        $tax = $salesOrder->asset_tax;

        return $tax
            ? $this->item(
                $tax,
                AssetTaxTransformer::inst()
                    ->showFields(
                        $this->includeFields['asset_tax'] ?? []
                    ))
            : $this->null();
    }

    public function includeMySalesChannel(SalesOrder $salesOrder)
    {
        $mySalesChannel = $salesOrder->my_sales_channel;

        return $mySalesChannel
            ? $this->item(
                $mySalesChannel,
                MySalesChannelTransformer::inst()
                    ->showFields(
                        $this->includeFields['my_sales_channel'] ?? []
                    ))
            : $this->null();
    }


}