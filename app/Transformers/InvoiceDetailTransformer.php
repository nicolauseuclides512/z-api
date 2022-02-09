<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */


namespace App\Transformers;


use App\Models\InvoiceDetail;
use App\Transformers\Base\Transformer;

class InvoiceDetailTransformer extends Transformer
{
    const SIMPLE_FIELDS = [
        'invoice_detail_id',
        'invoice_id',
        'item_id',
        'item_name',
        'item_rate',
        'item_quantity',
        'discount_contact_id',
        'discount_amount_type',
        'discount_amount_value',
        'tax_id',
        'tax_amount',
        'item_weight',
        'item_weight_unit',
        'item_dimension_l',
        'item_dimension_w',
        'item_dimension_h',
        'uom',
        'amount',
        'remaining_stock_qty'
    ];

    protected $availableIncludes = [
        'item',
        'asset_tax',
        'invoice'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform(InvoiceDetail $model)
    {
        return $this->filterTransform([
            'invoice_detail_id' => $model->invoice_detail_id,
            'invoice_id' => $model->invoice_id,
            'item_id' => $model->item_id,
            'item_name' => $model->item_name,
            'item_rate' => $model->item_rate,
            'item_quantity' => $model->item_quantity,
            'discount_contact_id' => $model->discount_contact_id,
            'discount_amount_type' => $model->discount_amount_type,
            'discount_amount_value' => (float)$model->discount_amount_value,
            'tax_id' => $model->tax_id,
            'tax_amount' => (float)$model->tax_amount,
            'item_weight' => $model->item_weight,
            'item_weight_unit' => $model->item_weight_unit,
            'item_dimension_l' => $model->item_dimension_l,
            'item_dimension_w' => $model->item_dimension_w,
            'item_dimension_h' => $model->item_dimension_h,
            'uom' => $model->uom,
            'amount' => $model->amount,
            'remaining_stock_qty' => $model->remaining_stock_qty
        ]);
    }

    public function includeItem(InvoiceDetail $invoiceDetail)
    {
        $item = $invoiceDetail->item;

        if (!is_null($item)) {
            return $this->item(
                $item,
                ItemTransformer::inst()
                    ->showFields(
                        $this->includeFields['item']
                    ));
        }

        return $this->null();
    }

    public function includeInvoice(InvoiceDetail $invoiceDetail)
    {
        $invoice = $invoiceDetail->invoice;

        if (!is_null($invoice)) {
            return $this->item(
                $invoice,
                InvoiceTransformer::inst()
                    ->showFields(
                        $this->includeFields['invoice']
                    ));
        }

        return $this->null();
    }


    public function includeTax(InvoiceDetail $invoiceDetail)
    {
        $tax = $invoiceDetail->tax;

        if (!is_null($tax)) {
            return $this->item(
                $tax,
                InvoiceTransformer::inst()
                    ->showFields(
                        $this->includeFields['asset_tax']
                    ));
        }

        return $this->null();
    }

}