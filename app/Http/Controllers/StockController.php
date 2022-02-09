<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Http\Controllers;

use App\Cores\HasFilterRequest;
use App\Cores\Jsonable;
use App\Domain\Commands;
use App\Domain\Contracts\StockContract;
use App\Domain\Data\StockKeyParam;
use App\Exceptions\AppException;
use Arseto\LumenCQRS\CommandBus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller;

class StockController extends Controller
{
    use Jsonable;
    use HasFilterRequest;

    private $stockService;
    private $commandBus;

    protected $filterCfg;

    protected $sortBy = [
        "created_at",
        "updated_at",
    ];

    public function __construct(
        StockContract $stockService,
        CommandBus $commandBus
    )
    {
        $this->stockService = $stockService;
        $this->filterCfg = [
            'all' => 'ALL',
        ];
        $this->commandBus = $commandBus;
    }

    public function detail(Request $request)
    {
        try {
            $param = StockKeyParam::fromRequest($request);
            $result = $this->stockService->detail($param);

            return $this->json(
                Response::HTTP_OK,
                'Stock loaded',
                $result
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function fetch(Request $request)
    {
        try {
            $modifiedRequest = $this->translateFilterRequest(
                $request,
                $this->sortBy,
                $this->filterCfg
            );
            $result = $this->stockService->fetch($modifiedRequest);

            return $this->json(Response::HTTP_OK,
                'Stock fetched',
                $result,
                $modifiedRequest
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function freeAdjust(Request $request)
    {
        try {

            $validate = Validator::make(
                $request->input(), [
                'adjust_qty' => 'required|integer|min:1',
                'item_id' => 'required|integer|exists:items,item_id'
            ]);

            if ($validate->fails()) {
                throw AppException::inst(
                    "Bad Request.",
                    Response::HTTP_BAD_REQUEST,
                    $validate->getMessageBag());
            }

//            $existingStock = $this->stockService->detail(
//                StockKeyParam::fromRequest($request)
//            );
//
//            if (is_null($existingStock)) {
//                throw AppException::inst("item stock does not exist.", Response::HTTP_BAD_REQUEST);
//            }
//
//            if ((int)$request->input('adjust_qty') <= $existingStock->quantity) {
//                throw AppException::inst("adjust qty param must be greater than quantity stock", Response::HTTP_BAD_REQUEST);
//            }

            $cmd = Commands\FreeAdjustStockCommand::fromRequest($request);
            $result = $this->commandBus->execute($cmd);
            return $this->json(
                Response::HTTP_CREATED,
                trans('messages.stock_added'),
                $result
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }
}
