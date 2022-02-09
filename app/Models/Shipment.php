<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Services\Gateway\Rest\RestService;
use App\Utils\DateTimeUtil;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class Shipment extends MasterModel
{
    const URI = 'com.zuragan.shipment';
    const NUMBERING_PREFIX = 'SHIP';

    protected $table = 'shipments';

    protected $primaryKey = 'shipment_id';

    protected $columnDefault = array("*");

    protected $columnSimple = array("*");

    protected $columnStatus = 'is_delivered';

    protected $appends = ['carrier'];

    public function __construct()
    {
        parent::__construct();

        $this->nestedBelongConfigs = array(
            "package" => array('*')
        );
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id')->nested();
    }

    public function rules($id = null)
    {
        $forUpdate = $id ? ',' . $id . ',shipment_id' : '';

        return array(
            'organization_id' => 'required|integer',
            'shipment_order_number' => 'required|string|max:50|org_unique:shipments,shipment_order_number' . $forUpdate,
            'date' => 'required|integer',
            'carrier_id' => 'required|integer|min:1',
            'tracking_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_delivered' => 'boolean',
            'package_id' => 'integer|exists:packages,package_id'
        );
    }

    public function getDateAttribute($v)
    {
        return DateTimeUtil::fromMicroSecond($v);
    }

    public function setDateAttribute($v)
    {
        $this->attributes['date'] = empty($v) ? null : DateTimeUtil::toMicroSecond($v);
    }

    public static function inst($loginInfo = null)
    {
        $newObj = new Shipment();
        if (!empty($loginInfo))
            $newObj->setLoginInfo($loginInfo);
        return $newObj;
    }

    public function getCarrierAttribute()
    {

        $cli = new RestService(
            new Client([
                'timeout' => Config::get('gateway.timeout'),
                'connect_timeout' =>
                    Config::get('gateway.connect_timeout',
                        Config::get('gateway.timeout')
                    )
            ])
        );

        $cli->setBaseUri(env('GATEWAY_ASSET_API'));

        $promise = [
            'carrier' => $cli->getAsync('/carriers/{id}', ['id' => $this->attributes['carrier_id']])
        ];

        $res = Promise\unwrap($promise);

        $carrier = array(json_decode($res['carrier']->getBody())->data) ?? [];

        return $carrier;
    }

    public function populate($request = [], BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);
        $model->organization_id = AuthToken::info()->organizationId;
        $model->shipment_order_number = $req->get('shipment_order_number');
        $model->date = $req->get('date');
        $model->carrier_id = (int)$req->get('carrier_id');
        $model->tracking_number = $req->get('tracking_number');
        $model->notes = $req->get('notes');
        $model->is_delivered = $req->get('is_delivered') === true ?: false;
        $model->package_id = $req->get('package_id');

        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();
        return $data;
    }

    public function scopeGetByIdAndPackageId($q, $id, $pkgId)
    {
        return $q->getInOrgRef()->where('package_id', $pkgId)->where('shipment_id', $id);
    }


}
