<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;


use App\Models\Invoice;
use App\Transformers\Base\Transformer;

class InvoiceTransformer extends Transformer
{

    const SORT_FIELDS = [
        'invoice_id',
        'invoice_number',
        'reference_number',
        'invoice_date',
        'due_date',
        'invoice_status',
        "total",
        'sub_total',
        'balance_due',
        'tax',
        'tax_included',
        'shipping_weight',
        'shipping_rate',
        'shipping_charge',
        'adjustment_name',
        'adjustment_value',
        'shipping_carrier_id',
        'shipping_carrier_name',
        'shipping_carrier_code',
        'shipping_carrier_service',
        'customer_notes',
        'term_and_condition'
    ];

    const SIMPLE_FIELDS = [
        'invoice_id',
        'organization_id',
        'user_id',
        'sales_order_id',
        'salesperson_id',
        'contact_id',
        'invoice_number',
        'reference_number',
        'invoice_date',
        'payment_term_id',
        'discount_contact_id',
        'discount_amount_type',
        'discount_amount_value',
        'shipping_weight',
        'shipping_rate',
        'shipping_charge',
        'adjustment_name',
        'adjustment_value',
        'total',
        'customer_notes',
        'term_and_condition',
        'due_date',
        'invoice_status',
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
        'shipping_phone',
        'shipping_mobile',
        'tax_included',
        'shipping_carrier_id',
        'shipping_carrier_name',
        'shipping_carrier_code',
        'shipping_carrier_service',
        'invoice_email',
        "total",
        'sub_total',
        "tax",
        'discount',
        'balance_due'
    ];

    protected $availableIncludes = [
        'discount_contact',
        'sales_order',
        'invoice_details',
        'payments',
        'contact'
    ];

    protected $defaultIncludes = [

    ];

    public static function inst()
    {
        return new self();
    }

    public function transform(Invoice $model)
    {
        return $this->filterTransform([
            'invoice_id' => $model->invoice_id,
            'organization_id' => $model->organization_id,
            'user_id' => $model->user_id,
            'sales_order_id' => $model->sales_order_id,
            'salesperson_id' => $model->salesperson_id,
            'contact_id' => $model->contact_id,
            'invoice_number' => $model->invoice_number,
            'reference_number' => $model->reference_number,
            'invoice_date' => $model->invoice_date,
            'payment_term_id' => $model->payment_term_id,
            'discount_contact_id' => $model->discount_contact_id,
            'discount_amount_type' => $model->discount_amount_type,
            'discount_amount_value' => (float)$model->discount_amount_valu,
            'shipping_weight' => $model->shipping_weight,
            'shipping_rate' => (float)$model->shipping_rate,
            'adjustment_name' => $model->adjustment_name,
            'adjustment_value' => (float)$model->adjustment_value,
            'customer_notes' => $model->customer_notes,
            'term_and_condition' => $model->term_and_condition,
            'due_date' => $model->due_date,
            'invoice_status' => $model->invoice_status,
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
            'shipping_phone' => $model->shipping_phone,
            'shipping_mobile' => $model->shipping_mobile,
            'tax_included' => $model->tax_included,
            'shipping_carrier_id' => $model->shipping_carrier_id,
            'shipping_carrier_name' => $model->shipping_carrier_name,
            'shipping_carrier_code' => $model->shipping_carrier_code,
            'shipping_carrier_service' => $model->shipping_carrier_service,
            'invoice_email' => $model->invoice_email,
            "total" => $model->total,
            'sub_total' => $model->sub_total,
            "tax" => $model->tax,
            'shipping_charge' => $model->shipping_charge,
            'discount' => $model->discount,
            'balance_due' => $model->balance_due
        ]);
    }

    public function includeInvoiceDetails(Invoice $invoice)
    {
        $invoiceDetails = $invoice->invoice_details;

        if (!is_null($invoiceDetails)) {
            return $this->collection(
                $invoiceDetails,
                InvoiceDetailTransformer::inst());
        }

        return $this->null();
    }

    public function includePayments(Invoice $invoice)
    {
        $payments = $invoice->payments;

        if (!is_null($payments)) {
            return $this->collection(
                $payments,
                PaymentTransformer::inst()
                    ->showFields(
                        $this->includeFields['payments'] ?? []
                    ));
        }

        return $this->null();
    }

    public function includeContact(Invoice $invoice)
    {
        $contact = $invoice->contact;

        if (!is_null($contact)) {
            return $this->item(
                $contact,
                ContactTransformer::inst()
            );
        }

        return $this->null();
    }

    public function includeSalesOrder(Invoice $invoice)
    {
        $salesOrder = $invoice->sales_order;

        if (!is_null($salesOrder)) {
            return $this->item(
                $salesOrder,
                SalesOrderTransformer::inst()
                    ->showFields(
                        $this->includeFields['sales_order'] ?? []
                    ));
        }

        return $this->null();
    }

}