<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Services\Gateway\Rest;

use App\Exceptions\AppException;
use App\Services\Gateway\Base\BaseService;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;

class RestService extends BaseService
{
    use RestServiceTrait;

    public static function inst(Client $client = null)
    {
        if (empty($client)) {
            $client = new Client([
                'timeout' => Config::get('gateway.timeout'),
                'connect_timeout' =>
                    Config::get('gateway.connect_timeout',
                        Config::get('gateway.timeout')
                    )
            ]);
        }

        return new self($client);
    }

    /**
     * @param $carrier_id
     * @return null
     * @throws \Exception
     * @throws \Throwable
     */
    public function getCarrier($carrier_id)
    {
        try {

            if (empty($carrier_id)) {
                throw AppException::inst(
                    'Carrier id does not exist.',
                    Response::HTTP_BAD_REQUEST);
            }

            $this->setBaseUri(env('GATEWAY_ASSET_API'));

            $promise = [
                'carrier' => $this->getAsync('/carriers/{id}', ['id' => $carrier_id])
            ];

            $res = Promise\unwrap($promise);

            $carrier = json_decode($res['carrier']->getBody())->data ?? null;

            return $carrier;

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param $carrier_code
     * @return null
     * @throws \Exception
     * @throws \Throwable
     */
    public function getCarrierByCode($carrier_code)
    {
        try {

            if (empty($carrier_code)) {
                throw AppException::inst(
                    'Carrier code request does not exist.',
                    Response::HTTP_BAD_REQUEST);
            }

            $this->setBaseUri(env('GATEWAY_ASSET_API'));

            $promise = [
                'carrier' => $this->getAsync('/carriers/code/{code}', ['code' => $carrier_code])
            ];

            $res = Promise\unwrap($promise);

            $carrier = json_decode($res['carrier']->getBody())->data ?: null;

            if (empty((array)$carrier))
                throw AppException::inst(
                    "Carrier code `$carrier_code` does not exist in our service.",
                    Response::HTTP_BAD_REQUEST
                );

            return $carrier;

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param $countryId
     * @param $provinceId
     * @param $districtId
     * @param $regionId
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function getArea($countryId, $provinceId, $districtId, $regionId)
    {
        try {
            $this->setBaseUri(env('GATEWAY_ASSET_API'));

            $promise = [
                'area' => $this->getAsync('/countries/areas',
                    [
                        'countryId' => $countryId,
                        'provinceId' => $provinceId,
                        'districtId' => $districtId,
                        'regionId' => $regionId,
                    ]
                )
            ];

            $res = Promise\unwrap($promise);

            $area = json_decode($res['area']->getBody())->data ?? [];

            return $area;

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }
}