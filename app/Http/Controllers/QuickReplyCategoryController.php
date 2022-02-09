<?php
/**
 * Created by IntelliJ IDEA.
 * User: nicolaus
 * Date: 8/16/18
 * Time: 10:13 AM
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\PatternController;
use App\Http\Controllers\Base\RestFulControl;
use App\Models\QuickReplyCategory;
use Illuminate\Http\Request;

class QuickReplyCategoryController extends BaseController implements PatternController
{
    use RestFulControl;

    public $configName = "quick_reply_categories";

    public $statusField = "category_status";

    public $requiredParamFetch = array();

    public $requiredParamMark = array("active", "inactive");

    protected $sortBy = [
        "created_at",
        "name",
        "category_id"
    ];

    public function __construct(Request $request)
    {
        parent::__construct($modelName = QuickReplyCategory::inst(), $request, $useAuth = true);
    }
}