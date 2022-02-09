<?php
/**
 * @author Jehan Afwazi Ahmad (jee.archer@gmail.com).
 */

namespace App\Http\Controllers\Open;

use App\Cores\HasFilterRequest;
use App\Cores\Jsonable;
use App\Domain\Contracts\MySalesChannelContract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;

class ShopController extends Controller
{
    use Jsonable;
    use HasFilterRequest;

    private $myScService;

    protected $filterCfg;

    protected $sortBy = [
        "created_at",
        "updated_at",
        "id",
        "store_name",
        "sales_channel_id",
    ];

    public function __construct(
        MySalesChannelContract $myScService
    )
    {
        $this->myScService = $myScService;
        $this->filterCfg = config('filters.my_sales_channels');
    }

    public function fetch(Request $request)
    {
        try {
            $filterRequest = $this->translateFilterRequest(
                $request,
                $this->sortBy,
                $this->filterCfg
            );
            $result = $this->myScService->homeShop($filterRequest);

            return $this->json(Response::HTTP_OK,
                'My Sales Channel data fetched',
                $result,
                $filterRequest
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

}
