<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */


namespace App\Transformers;

use App\Models\SalesOrderDetail;
use App\Transformers\Base\Transformer;

class SalesOrderDetailTransformer extends Transformer
{

    const SORT_FIELDS = [
        'sales_order_detail_id',
        'sales_order_id',
        'item_id',
        'item_name',
        'item_rate',
        'item_quantity',
        'discount_contact_id',
        'discount_amount_type',
        'discount_amount_value',
        'uom',
        'amount',
        'remaining_stock_qty'
    ];

    const SIMPLE_FIELDS = [

    ];

    protected $availableIncludes = [
        'item',
        'sales_order',
        'tax'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform(SalesOrderDetail $model)
    {
        return $this->filterTransform([
            'sales_order_detail_id' => $model->sales_order_detail_id,
            'sales_order_id' => $model->sales_order_id,
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

    public function includeItem(SalesOrderDetail $salesOrderDetail)
    {
        $item = $salesOrderDetail->item;

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

    public function includeSalesOrder(SalesOrderDetail $salesOrderDetail)
    {
        $salesOrder = $salesOrderDetail->sales_order;

        if (!is_null($salesOrder)) {
            return $this->item(
                $salesOrder,
                InvoiceTransformer::inst()
                    ->showFields(
                        $this->includeFields['sales_order']
                    ));
        }

        return $this->null();
    }


    public function includeTax(SalesOrderDetail $salesOrderDetail)
    {
        $tax = $salesOrderDetail->tax;

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