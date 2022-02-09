<?php
/**
 * Created by PhpStorm.
 * User: nicolaus
 * Date: 20/08/18
 * Time: 14:26
 */

namespace App\Http\Controllers;


use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\PatternController;
use App\Http\Controllers\Base\RestFulControl;
use App\Models\QuickReply;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class QuickReplyController extends BaseController implements PatternController
{
    use RestFulControl;

    public $configName = "quick_reply";

    public $statusField = "status";

    public $requiredParamFetch = array();

    public $requiredParamMark = array("active", "inactive");

    protected $sortBy = [
        "created_at",
        "name",
        "quick_reply_id"
    ];

    public function __construct(Request $request)
    {
        parent::__construct($modelName = QuickReply::inst(), $request, $useAuth = true);
    }

    public function fetch()
    {
        try {
            $data = $this->model;

            if ($this->useNestedOnList) {
                $data = $data->nested();
            }

            $category_id = $this->request->input('category_id');

            switch ($category_id){
                case "all":
                    $data = $data
                        ->filter($this->requestMod()['filter_by'],
                            $this->requestMod()['q'])
                        ->orderBy(
                            $this->requestMod()['sort_column'],
                            $this->requestMod()['sort_order'])
                        ->paginate($this->request->input("per_page"));
                break;
                default :
                    $data = $data->where('category_id', $this->request->input('category_id'))
                        ->filter($this->requestMod()['filter_by'],
                            $this->requestMod()['q'])
                        ->orderBy(
                            $this->requestMod()['sort_column'],
                            $this->requestMod()['sort_order'])
                        ->paginate($this->request->input("per_page"));
                break;
            }

            if (!empty($data)) {
                Log::info($this->configName . " fetched");
                return $this->json(Response::HTTP_OK,
                    $this->configName . " fetched.", $data);
            }

            Log::error($this->configName . " Not Found");
            return $this->json(Response::HTTP_BAD_REQUEST,
                $this->configName . " Not Found", $data);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }
}