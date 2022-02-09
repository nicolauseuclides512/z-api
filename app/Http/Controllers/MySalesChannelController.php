<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */
namespace App\Http\Controllers;

use App\Cores\Jsonable;
use App\Cores\HasFilterRequest;
use App\Domain\Contracts\SalesChannelContract;
use App\Domain\Contracts\MySalesChannelContract;
use App\Domain\Data\MySalesChannelData;
use http\Message;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class MySalesChannelController extends Controller
{
    use Jsonable;
    use HasFilterRequest;

    private $scService;
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
        SalesChannelContract $scService,
        MySalesChannelContract $myScService
    ){
        $this->scService = $scService;
        $this->myScService = $myScService;
        $this->filterCfg = config('filters.my_sales_channels');
    }

    public function create()
    {
        try {
            $channels = $this->scService->all();

            return $this->json(
                Response::HTTP_OK,
                'Resource for create my sales channel loaded', [
                'channels' => $channels,
            ]);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = MySalesChannelData::new($request);
            $result = $this->myScService->create($data);

            return $this->json(
                Response::HTTP_CREATED,
                trans('messages.sales_channel_created'), $result);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $data = MySalesChannelData::update($id, $request);
            $result = $this->myScService->update($data);

            return $this->json(
                Response::HTTP_CREATED,
                'My Sales channel data updated', $result);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function detail($id)
    {
        try {
            $result = $this->myScService->detail($id);

            return $this->json(Response::HTTP_OK,
                'My Sales Channel data loaded',
                $result
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function delete($id)
    {
        try {
            $this->myScService->delete($id);

            return $this->json(Response::HTTP_OK,
                trans('messages.sales_channel_deleted')
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions([],
                Response::HTTP_UNPROCESSABLE_ENTITY,
                trans('messages.sales_channel_cannot_delete'));
        }
    }

    public function fetch(Request $request)
    {
        try {
            $filterRequest = $this->translateFilterRequest(
                $request,
                $this->sortBy,
                $this->filterCfg
            );

            $result = $this->myScService->fetch($filterRequest);
            $sort = $request->input('sort');
            if ($sort == 'display_name.asc')
                $result = $result->sortBy('display_name')->values();
            elseif ($sort == 'display_name.desc')
                $result = $result->sortByDesc('display_name')->values();

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
