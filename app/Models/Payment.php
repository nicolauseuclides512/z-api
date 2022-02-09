<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Utils\DateTimeUtil;
use Illuminate\Support\Collection;

class Payment extends MasterModel
{
    const URI = 'com.zuragan.payment';
    const NUMBERING_PREFIX = 'PAY';

    protected $table = 'payments';

    protected $primaryKey = 'payment_id';

    protected $columnDefault = ['*'];

    protected $columnSimple = ['*'];

    protected $fillable = [
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
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function getDateAttribute($v)
    {
        return DateTimeUtil::fromMicroSecond($v);
    }

    public function getFormattedDateAttribute($v)
    {
        return date("j F Y", strtotime($this->date));
    }

    public function setDateAttribute($v)
    {
        $this->attributes['date'] = empty($v) ? null : DateTimeUtil::toMicroSecond($v);
    }

    public function getAmountAttribute($v)
    {
        //markup
        return round($v);
    }

    public function rules($id = null)
    {
        $forUpdate = $id ? ',' . $id . ',payment_id' : '';

        return [
            'invoice_id' => 'required|integer|exists:invoices,invoice_id',
            'payment_number' => 'required|string|max:50|org_unique:payments,payment_number' . $forUpdate,
            'reference_number' => 'nullable|string|max:100',
            'date' => 'required|numeric',
            'payment_mode_id' => 'required|integer',
            'payment_mode_name' => 'required|string',
            'payment_account_holder' => 'nullable|string',
            'payment_account_name' => 'nullable|string',
            'payment_account_number' => 'nullable',
            'payment_account_id' => 'nullable|string',
            'currency' => 'required|integer',
            'amount' => 'required|numeric|between:0,9999999999',
            'notes' => 'nullable|string'
//        'payment_status' => 'string'
        ];
    }

    public static function inst()
    {
        return new self();
    }

    public function populate($request = [], BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);
        $model->invoice_id = $req->get('invoice_id');
        $model->organization_id = AuthToken::info()->organizationId;
        $model->payment_number = $req->get('payment_number');
        $model->reference_number = $req->get('reference_number');
        $model->date = $req->get('date');
        $model->payment_mode_id = $req->get('payment_mode_id');
        $model->payment_mode_name = $req->get('payment_mode_name');
        $model->payment_account_id = $req->get('payment_account_id');
        $model->payment_account_holder = $req->get('payment_account_holder');
        $model->payment_account_name = $req->get('payment_account_name');
        $model->payment_account_number = $req->get('payment_account_number');
        $model->currency = $req->get('currency');
        $model->amount = (float)$req->get('amount');
        $model->notes = $req->get('notes');
//        $model->payment_status = $req->get('payment_status');

        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q;

        /*filtering code*/
        return $data;
    }

    public function getByInvoiceId($id)
    {
        return $this->where('invoice_id', $id);
    }

    public function getByIdAndInvoiceId($invId, $id)
    {
        return $this->where('invoice_id', $invId)->where('payment_id', $id);
    }
}
