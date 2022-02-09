<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Models;

use App\Utils\DateTimeUtil;

/**
 * @property int stock_adjustment_id
 * @property int organization_id
 * @property string stock_adjustment_number
 * @property date stock_adjustment_date
 * @property string reference_number
 * @property boolean is_applied
 * @property boolean is_void
 */
class StockAdjustment extends MasterModel
{
    const REASON_CATEGORY = 'ADJ';
    const URI = 'com.zuragan.stock_adjustment';
    const NUMBERING_PREFIX = 'SA';

    protected $table = 'stock_adjustments';
    protected $primaryKey = 'stock_adjustment_id';

    protected $fillable = [
        'reference_number',
        'is_applied',
        'is_void',
        'is_free_adjust',
        'notes',
    ];

    protected $attributes = [
        'is_free_adjust' => false,
    ];

    protected $guarded = [
        'stock_adjustment_date',
        'stock_adjustment_number',
    ];

    protected $appends = [
        'status',
        'reason_summary',
    ];

    protected $casts = [
        'is_applied' => 'boolean',
        'is_void' => 'boolean',
        'is_free_adjust' => 'boolean',
    ];

    public function getReasonSummaryAttribute()
    {
        $query = $this->details()
            ->leftJoin('reasons',
                'reasons.reason_id', '=', 'stock_adjustment_details.reason_id')
            ->groupBy('stock_adjustment_details.reason_id', 'reasons.reason')
            ->selectRaw('
                count(*) as line_count,
                sum(stock_adjustment_details.adjust_qty) as qty,
                reasons.reason as reason_description');

        $summary = $query->get();

        return $summary;
    }

    public function rules($id = null)
    {
        $forUpdate = $id ? ',' . $id . ',stock_adjustment_id' : '';

        $orgId = $this->getOrganizationId();
        $rules = [
            'stock_adjustment_number' => 'required|string|max:50|org_unique:stock_adjustments,stock_adjustment_number' . $forUpdate,
            'stock_adjustment_date' => 'required|numeric',
            'reference_number' => 'nullable|string',
            'is_applied' => 'required|boolean',
            'is_void' => 'required|boolean',
            'notes' => 'nullable|string|max:255'
        ];
        return $rules;
    }

    public function setStockAdjustmentDateAttribute($value)
    {
        $this->attributes['stock_adjustment_date'] = empty($value) ? null :
            DateTimeUtil::toMicroSecond($value);
    }

    public function getStockAdjustmentDateAttribute($value)
    {
        return DateTimeUtil::fromMicroSecond($value);
    }

    public function getStatusAttribute($value)
    {
        if ($this->is_void) {
            return 'VOID';
        }
        if ($this->is_applied) {
            return 'APPLIED';
        }
        return 'DRAFT';
    }

    public function details()
    {
        //eager load to show relation in json
        return $this->hasMany(StockAdjustmentDetail::class, 'stock_adjustment_id');
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();

        switch ($filterBy) {
            case 'DRAFT':
                $data = $data->where('is_applied', false)
                    ->where('is_void', false);
                break;
            case 'APPLIED':
                $data = $data->where('is_applied', true);
                break;
            case 'VOID':
                $data = $data->where('is_void', true);
                break;
        }

        if (!empty($key)) {
            $data = $data->where("stock_adjustment_number", "ILIKE", "%$key%")
                ->orWhere("reference_number", "ILIKE", "%$key%");
        }

        return $data;
    }

    public function scopeByReason($q, $key = '')
    {
        $data = $q->getInOrgRef();

        $data = $data->leftJoin('stock_adjustment_details',
            'stock_adjustment_details.stock_adjustment_id', '=',
            'stock_adjustments.stock_adjustment_id')
            ->leftJoin('reasons', 'reasons.reason_id', '=',
                'stock_adjustment_details.reason_id')
            ->where('is_applied', true)
            ->where('is_void', false);

        if (!empty($key)) {
            $data = $data->where(function ($query) use ($key) {
                $query->where("reasons.reason", "ILIKE", "%" . $key . "%");
            });
        }

        return $data->select(
            'stock_adjustment_date',
            'stock_adjustments.stock_adjustment_id',
            'stock_adjustment_number',
            'reference_number',
            'reason',
            'adjust_qty',
            'on_hand_qty',
            'is_void',
            'is_applied',
            'notes'
        );
    }

    public function scopeByItem($q, $key = '')
    {
        $data = $q->getInOrgRef();

        $data = $data->leftJoin('stock_adjustment_details',
            'stock_adjustment_details.stock_adjustment_id', '=',
            'stock_adjustments.stock_adjustment_id')
            ->leftJoin('items', 'items.item_id', '=', 'stock_adjustment_details.item_id')
            ->leftJoin('reasons', 'reasons.reason_id', '=',
                'stock_adjustment_details.reason_id');
//            ->where('is_applied', true)
//            ->where('is_void', false);

        if (!empty($key)) {
            $data = $data->where(function ($query) use ($key) {
                $query->where("items.item_name", "ILIKE", "%" . $key . "%")
                    ->orWhere("items.code_sku", "ILIKE", "%" . $key . "%");
            });
        }

        return $data->select(
            'stock_adjustment_date',
            'stock_adjustments.stock_adjustment_id',
            'stock_adjustment_number',
            'reference_number',
            'stock_adjustment_details.item_id',
            'item_name',
            'reason',
            'adjust_qty',
            'on_hand_qty',
            'is_void',
            'is_applied',
            'notes'
        );
    }
}
