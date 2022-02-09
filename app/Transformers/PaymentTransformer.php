<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;


use App\Models\Payment;
use App\Transformers\Base\Transformer;

class PaymentTransformer extends Transformer
{
    const SIMPLE_FIELDS = [
        'payment_id',
        'invoice_id',
        'payment_number',
        'reference_number',
        'date',
        'payment_mode_id',
        'payment_mode_name',
        'payment_account_holder',
        'payment_account_name',
        'payment_account_number',
        'payment_account_id',
        'currency',
        'amount',
        'notes',
        'payment_status',
    ];

    protected $availableIncludes = [
        'invoice'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform(Payment $model)
    {
        return $this->filterTransform([
            'payment_id' => $model->payment_id,
            'invoice_id' => $model->invoice_id,
            'payment_number' => $model->payment_number,
            'reference_number' => $model->reference_number,
            'date' => $model->date,
            'payment_mode_id' => $model->payment_mode_id,
            'payment_mode_name' => $model->payment_mode_name,
            'payment_account_holder' => $model->payment_account_holder,
            'payment_account_name' => $model->payment_account_name,
            'payment_account_number' => $model->payment_account_number,
            'payment_account_id' => $model->payment_account_id,
            'currency' => $model->currency,
            'amount' => $model->amount,
            'notes' => $model->notes,
            'payment_status' => $model->payment_status
        ]);
    }

    public function includeInvoice(Payment $payment)
    {
        $invoice = $payment->invoice;

        if (!is_null($invoice)) {
            return $this->item($invoice,
                InvoiceDetailTransformer::inst()->showFields(
                    $this->includeFields['invoice']
                ));
        }

        return $this->null();
    }
}