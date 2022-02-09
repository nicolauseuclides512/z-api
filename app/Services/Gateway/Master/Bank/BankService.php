<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Services\Gateway\Master\Bank;


use App\Exceptions\AppException;
use App\Services\Gateway\Master\DefaultService;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;

class BankService extends DefaultService implements BankServiceContract
{


    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->setBaseUri(env('GATEWAY_ASSET_API'));
    }

    /**
     * @param $bankName
     * @return null
     * @throws \Exception
     * @throws \Throwable
     */
    public function getLogoByName($bankName)
    {
        try {

            if (empty($bankName)) {
                throw AppException::inst(
                    'Bank name does not exist.',
                    Response::HTTP_BAD_REQUEST);
            }

            $promise = [
                'bank' => $this->getAsync('/banks/name/{name}', ['name' => $bankName])
            ];

            $res = Promise\unwrap($promise);

            $bank = json_decode($res['bank']->getBody())->data ?? null;

            return $bank->logo ?? null;

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        } catch (\Throwable $e) {
            throw $e;
        }

    }
}