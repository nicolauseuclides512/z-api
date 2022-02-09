<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Models;

/**
 * @property id
 * @property organization_id
 * @property sales_channel_id
 * @property store_name
 * @method static paginate($int)
 */
class MySalesChannel extends MasterModel
{
    protected $table = 'my_sales_channels';

    protected $fillable = [
        'sales_channel_id',
        'store_name',
        'display_mode',
        'order',
        'is_shown',
        'external_link'
    ];

    protected $appends = [
        'display_name'
    ];

    public function rules($id = null)
    {
        return [
            'organization_id' => 'required|integer',
            'sales_channel_id' => 'required|integer|exists:sales_channels,id',
            'store_name' => 'required|string',
            'display_mode' => 'required|integer|in:1,2', //1. name, 2. relation name
            'order' => 'required|integer',
            'is_shown' => 'required|boolean',
            'external_link' => 'string|nullable',
        ];
    }

    public function sales_channel()
    {
        return $this->belongsTo(SalesChannel::class);
    }

    public function getDisplayNameAttribute()
    {
        if ($this->display_mode == 2)
            return $this->sales_channel()->pluck('channel_name')->first();

        return $this->store_name;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();

        if (!empty($key)) {
            $data = $data->where(function ($q) use ($key) {
                return $q->where("store_name", "ILIKE", "%$key%")
                    ->orWhereHas('sales_channel', function ($query) use ($key) {
                        return $query->where("channel_name", "ILIKE", "%$key%");
                    });
            });
        }

        return $data;
    }


}
