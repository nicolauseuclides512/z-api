<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Models\BaseTraits\ObserveModelTrait;
use App\Models\BaseTraits\RestModelTrait;
use App\Models\Contract\RestModelContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterModel extends BaseModel implements RestModelContract
{

    use SoftDeletes, ObserveModelTrait, RestModelTrait;

    protected $dates = ['created_at, updated_at, deleted_at'];

    protected $dateFormat = 'U';

    public static function boot()
    {
        parent::boot();
        self::bootObservable();
    }

    public static function inst()
    {
        return new self();
    }

}
