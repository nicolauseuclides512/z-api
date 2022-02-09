<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Support\Collection;


class SalesChannel extends MasterModel
{
    protected $table = 'sales_channels';

    protected $fillable = [
        'channel_name',
        'logo',
    ];

    protected $primaryKey = 'id';

    protected $columnDefault = [
        "id",
        "channel_name",
        "logo",
    ];

    protected $columnSimple = [
        "id",
        "channel_name",
        "logo",
    ];

    public static function inst()
    {
        return new self();
    }

    public function rules($id = null)
    {
        $forUpdate = $id ? ',' . $id . ',id' : '';

        return [
            'channel_name' => 'required|string|unique:sales_channels,channel_name' . $forUpdate,
            'logo' => 'nullable|string',
        ];
    }

    protected $softDeleteCascades = [];

    public function populate($request = [], BaseModel $model = null)
    {
        $req = new Collection($request);

        if (is_null($model))
            $model = self::inst();

        $model->channel_name = $req->get('channel_name');
        $model->logo = $req->get('logo');

        return $model;
    }


}
