<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */


namespace App\Http\Controllers;


use App\Cores\Jsonable;
use App\Exceptions\AppException;
use App\Models\AssetPaymentTerm;
use App\Models\AssetUom;
use App\Models\AuthToken;
use App\Models\Setting;
use App\Models\Reason;
use App\Domain\Contracts\StockAdjustmentContract;
use App\Domain\Contracts\SalesOrderContract;
use App\Domain\Contracts\PaymentContract;
use App\Domain\Contracts\ShipmentContract;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InitController
{
    use Jsonable;

    public function setup()
    {
        try {

            if (!Setting::setupDefaultData())
                return $this->json(
                    Response::HTTP_ACCEPTED,
                    'Organization exist, nothing todo ');

            Log::info('Setup data completed');
            return $this->json(
                Response::HTTP_CREATED,
                'Setup completed.');


        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->jsonExceptions($e);
        }
    }

}
