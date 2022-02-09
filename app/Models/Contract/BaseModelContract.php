<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Models\Contract;


use App\Models\Base\BaseModel;

interface BaseModelContract
{
    public function rules($id = null);

    public static function inst();

    public function populate($request = array(), BaseModel $model = null);

    public function scopeFilter($q, $filterBy = "", $query = "");

}