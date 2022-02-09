<?php

namespace App\Models;

use App\Domain\Contracts\StockContract;
use App\Domain\Data\StockKeyParam;
use App\Domain\ValueObjects\AdjustStockValue;
use App\Exceptions\AppException;
use App\Mails\ReportImportMassMail;
use App\Models\Base\BaseModel;
use App\Models\Contract\ItemContract;
use App\Utils\MediaUtil;
use Carbon\Carbon;
use Excel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class Item extends MasterModel implements ItemContract
{
    //item status
    const ITEM_ALL = 9;
    const ITEM_INACTIVE = 0;
    const ITEM_ACTIVE = 1;
    const ITEM_LOW_STOCK = 2;
    const ITEM_LOW_UN_GROUP = 3;
    const ITEM_SALES = 4;
    const ITEM_PURCHASE = 5;
    const ITEM_SALES_AND_PURCHASE = 6;
    const ITEM_INVENTORY = 7;

    protected $table = 'items';

    protected $primaryKey = 'item_id';

    protected $fillable = [
        'sku_code',
        "uom_id",
        "tax_id",
        "item_name",
//        "weight",
//        "weight_unit",
        "dimension_l",
        "dimension_w",
        "dimension_h",
        "compare_rate",
        "sales_rate",
        "track_inventory",
        "inventory_stock_warning",
        "item_status",
        "category_id",
        "organization_id",
        "parent_id",
        "item_attributes",
        "description",
        "code_sku",
        "barcode",
        "page_title",
        "meta_description",
        "slug",
//        "visibility",
        "tags",
        "is_shown_in_shop", //untuk catalog shop jika ingin ditampilkan
    ];

    protected $columnDefault = [
        "item_id",
        "uom_id",
        "tax_id",
        "item_name",
        "weight",
        "weight_unit",
        "dimension_l",
        "dimension_w",
        "dimension_h",
        "compare_rate",
        "sales_rate",

        "track_inventory",
        "inventory_stock_warning",
        "item_status",
        "category_id",
        "organization_id",
        "parent_id",
        "item_attributes",
        "description",
        "code_sku",
        "barcode",
        "page_title",
        "meta_description",
        "slug",
        "is_shown_in_shop",
//        "visibility",
        "tags",

//        "inventory_stock",
//        "sales_checked",
//        "sales_account",
//        "sales_description",
//        "purchase_checked",
//        "purchase_rate",
//        "purchase_account",
//        "purchase_description",
//        "inventory_checked",
//        "inventory_rate",
//        "inventory_account",
    ];

    protected $columnStatus = 'item_status';

    protected $columnSimple = [
        "item_id",
        "item_name",
        "parent_id",
        "code_sku",
        'uom_id',
        'category_id',
    ];

    protected $appends = [
        'item_type',
        'stock_quantity',
        'primary_images'
    ];

    protected $casts = [
        'organization_id' => 'integer',
        'uom_id' => 'integer',
        'tax_id' => 'integer',
        'item_name' => 'string',
        'item_attributes' => 'string',
        'weight' => 'integer',
        'weight_unit' => 'string',
        'dimension_l' => 'integer',
        'dimension_w' => 'integer',
        'dimension_h' => 'integer',
        'code_sku' => 'string',
        'sales_rate' => 'float',
        'track_inventory' => 'boolean',
//        'inventory_stock' => 'integer',
        'inventory_stock_warning' => 'integer',
        'parent_id' => 'integer',
        'category_id' => 'integer',
        'description' => 'string',
        'compare_rate' => 'float',
        'barcode' => 'string',
        'page_title' => 'string',
        'meta_description' => 'string',
        'slug' => 'string',
        'is_shown_in_shop' => 'boolean',
//        'visibility' => 'string',
        'tags' => 'string',

//            'sales_checked' => 'sometimes|boolean', # value from checkbox
//            'sales_account' => 'required_if:sales_checked,true|integer',
//            'sales_description' => 'string',
//            'purchase_checked' => 'sometimes|boolean', # value from checkbox
//            'purchase_rate' => 'required_if:purchase_checked,true|numeric',
//            'purchase_account' => 'required_if:purchase_checked,true|integer',
//            'purchase_description' => 'string',
//            'inventory_checked' => 'sometimes|boolean', # value from checkbox
//            'inventory_rate' => 'numeric',
//            'inventory_account' => 'required_if:inventory_checked,true|integer',
//            'item_status' => 'integer|in:0,1',

    ];

    protected $softDeleteCascades = [];

    private $stockService;

    public function __construct()
    {
        parent::__construct();
        $this->nestedBelongConfigs = [
//            "organization" => ["organization_id", "name"],
            "asset_tax" => AssetTax::inst()->getColumnSimple(),
            "asset_uom" => AssetUom::inst()->getColumnSimple(),
            "asset_category" => AssetCategory::inst()->getColumnSimple(),
        ];
        $this->nestedHasManyConfigs = [
            "item_medias" => array("*"),
            "item_rates" => array("*"),
//            "sales_quotations" => array("sales_quotation_id", "sales_quotation_name"),
//            "sales_orders" => array("sales_order_id", "sales_order_name"),
//            "invoices" => array("invoice_id", "invoice_name"),
//            "purchase_orders" => array("purchase_order_id", "purchase_order_name")
        ];
    }

    public static function boot()
    {
        parent::boot();

        self::saving(function (Item $model) {

            #set validation
            $validation = Validator::make($model->attributes, self::rules($model->item_id));

            #additional rules
            $validation->sometimes(['attribute_type', 'attribute_option'], 'required|string', function ($input) {
                return $input->itemgroup_id == true;
            });

            foreach ($model->attributes as $key => $value) {
                $model->{$key} = $value === '' ? null : $value;
            }

            foreach ($model->attributes as $key => $value) {
                $model->{$key} = $value === '' ? null : $value;
            }

            #cheking validation
            if ($validation->fails()) {
                $model->errors = $validation->messages();
                return false;
            } else {
                return true;
            }
        });

        self::deleting(function (Item $item) { // before delete() method call this
            //should prevent deletion if has children
            if ($item->hasChildren()) {
                throw new AppException(
                    "This item has variation, please delete the related variation first.",
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            //should prevent deletion if exist in sales order
            if ($item->hasSalesOrderDetail() || $item->hasInvoiceDetail()) {
                throw new AppException(
                    trans('messages.item_cannot_delete'),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            // do the rest of the cleanup...
        });
    }

    public static function inst()
    {
        return new self();
    }

    public function rules($id = null)
    {
        $forUpdate = $id ? ',' . $id . ',item_id' : '';
        $isInventory = AuthToken::isInventory();

        return [
            'organization_id' => 'required|integer',
            'uom_id' => 'required|integer|exists:asset_uoms,uom_id',
            'tax_id' => 'nullable|integer|exists:asset_taxes,tax_id',
            'item_name' => 'required|string|max:100',
            'item_attributes' => 'string',
            'weight' => 'sometimes|nullable|integer|min:1',
            'weight_unit' => 'sometimes|nullable|string|in:gr,kg',
            'dimension_l' => 'integer|min:0',
            'dimension_w' => 'integer|min:0',
            'dimension_h' => 'integer|min:0',
            'code_sku' => 'nullable|string|max:50|org_unique:items,code_sku' . $forUpdate,
            'sales_rate' => 'numeric|between:0,9999999999',
            'track_inventory' => $isInventory ? 'sometimes|required|boolean' : 'sometimes|nullable',
            'inventory_stock_warning' => 'integer|min:0',
            'parent_id' => 'nullable|integer|exists:items,item_id',
            'category_id' => 'nullable|integer|exists:asset_categories,category_id',
            'description' => 'string',
            'compare_rate' => 'numeric|between:0,9999999999',
            'barcode' => 'string',
            'page_title' => 'string',
            'meta_description' => 'string',
            'slug' => 'string|org_unique:items,slug' . $forUpdate,
//            'visibility' => 'string',
            'tags' => 'string',
            'stock_quantity' => 'numeric|nullable',
            'is_shown_in_shop' => 'required|integer|in:0,1',

            //            'sales_checked' => 'sometimes|boolean', # value from checkbox
            //            'sales_account' => 'required_if:sales_checked,true|integer|exists:asset_accounts,account_id',
            //            'sales_description' => 'string|max:500',
            //            'purchase_checked' => 'sometimes|boolean', # value from checkbox
            //            'purchase_rate' => 'required_if:purchase_checked,true|numeric|min:1',
            //            'purchase_account' => 'required_if:purchase_checked,true|integer|exists:asset_accounts,account_id',
            //            'purchase_description' => 'string|max:500',
            //            'inventory_checked' => 'sometimes|boolean', # value from checkbox
            //            'inventory_rate' => 'numeric|min:0',
            //            'inventory_account' => 'required_if:inventory_checked,true|integer|exists:asset_accounts,account_id|min:0',
            //            'item_status' => 'required|integer|in:0,1',

        ];
    }

    private function getStockService()
    {
        if (is_null($this->stockService)) {
            $this->stockService = app(StockContract::class);
        }
        return $this->stockService;
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function item_rates()
    {
        return $this->hasMany(ItemRate::class, 'item_id');
    }

    public function item_medias()
    {
        return $this->hasMany(ItemMedia::class, 'item_id');
    }

    public function sales_order_details()
    {
        return $this->hasOne(SalesOrderDetail::class, 'item_id');
    }

    public function invoice_details()
    {
        return $this->hasOne(InvoiceDetail::class, 'item_id');
    }

    public function asset_tax()
    {
        return $this->belongsTo(AssetTax::class, 'tax_id');
    }

    public function asset_uom()
    {
        return $this->belongsTo(AssetUom::class, 'uom_id');
    }

    public function asset_category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function parent()
    {
        return $this->hasOne(Item::class, 'item_id', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Item::class, 'parent_id', 'item_id');
    }

    public function getItemAttributesAttribute($v)
    {
        return !is_string($v) ?? json_decode($v);
    }

    public function getVisibilityAttribute($v)
    {
        return !is_string($v) ?? json_decode($v);
    }

    public function getSalesRateAttribute($v)
    {
        return round($v);
    }

    public function getCompareAttribute($v)
    {
        return round($v);
    }

    public function getStockQuantityAttribute()
    {
        $stock = $this->getStock();
        if ($stock) {
            return $stock->quantity;
        }
        return 0;
    }

    public function getPrimaryImagesAttribute()
    {
        $media = $this->item_medias()->where('is_main', true)->first();
        return isset($media->multi_res_image) ? $media->multi_res_image->toArray() : NULL;
    }

    public function getItemTypeAttribute()
    {
        $type = null;
        if ($this->sales_checked && $this->purchase_checked && $this->inventory_checked) {
            $type = "Inventory Items";
        } elseif ($this->sales_checked && $this->purchase_checked) {
            $type = "Sales And Purchase Items";
        } elseif ($this->sales_checked) {
            $type = "Sales Items";
        } elseif ($this->purchase_checked) {
            $type = "Purchase Items";
        } else {
            $type = null;
        }

        return $this->attributes['item_type'] = $type;
    }

    public function hasSalesOrderDetail()
    {
        return $this->sales_order_details()->exists();
    }

    public function hasChildren()
    {
        return $this->children()->exists();
    }

    public function hasInvoiceDetail()
    {
        return $this->invoice_details()->exists();
    }

    public function getItemInOrg($id)
    {
        $itemExist = self::where("item_id", "=", $id)
            ->where("organization_id", "=", AuthToken::info()->organizationId)
            ->nested()
            ->first();

        if (!empty($itemExist)) {
            return $itemExist;
        }
        return null;
    }

    public function getItemByParentId($parentId)
    {
        return $this->where('parent_id', $parentId)->get();
    }

    public function scopeColumnSimple($q)
    {
        return $q->select($this->columnSimple);
    }

    public function scopeChild($q, array $column = ["*"], $key = "", $searchColumn = "")
    {
        $key = strtolower($key);
        if (empty($column)) $column = $this->columnDefault;
        return $q->with([
            'children' => function ($q) use ($column, $key, $searchColumn) {
                if (!empty($key))
                    $q = $q->where($searchColumn, $key);

                return $q->select($column);
            }
        ]);
    }

    public function scopeFilter($q, $filterBy = "", $key = "", $leafOnly = false)
    {
        $data = $q->getInOrgRef();

        switch ($filterBy) {
            case Item::ITEM_ACTIVE :
                $data = $data->where("item_status", "=", true);
                break;
            case Item::ITEM_INACTIVE :
                $data = $data->where("item_status", "=", false);
                break;
            case Item::ITEM_PURCHASE :
                $data = $data->where("purchase_checked", "=", 1);
                break;
            case Item::ITEM_SALES :
                $data = $data->where("sales_checked", "=", 1);
                break;
            case Item::ITEM_INVENTORY :
                $data = $data->where("inventory_checked", "=", 1);
                break;
            case Item::ITEM_LOW_STOCK :
                $data = $data->where("sales_rate", "=", 1);
                break;
            case Item::ITEM_LOW_UN_GROUP :
                $data = $data->where("sales_rate", "=", 1);
                break;
        }

        if (!empty($key)) {
            $key = strtolower($key);
            $data = $data
                ->where(function ($query) use ($key) {
                    $query
                        ->where("item_name", "ILIKE", "%" . $key . "%")
                        ->orWhere("code_sku", "ILIKE", "%" . $key . "%")
                        ->orWhereHas('asset_category',
                            function ($query) use ($key) {
                                return $query->where('name', 'ILIKE', '%' . $key . '%');
                            })
                        ->orWhereHas('asset_uom',
                            function ($query) use ($key) {
                                return $query->where('name', 'ILIKE', '%' . $key . '%');
                            });
                });

            return $data
                ->doesntHave('children')
                ->child(["*"], $key, 'item_name');
        }

        if ($leafOnly)
            $data = $data
                ->doesntHave('children');
        else
            $data = $data
                ->where('parent_id', null)
                ->whereOr('parent_id', 0);

        return $data->child();
    }

    public function scopeGetBySkuCode($q, $skuCode)
    {
        return $q->getInOrgRef()->where('code_sku', $skuCode)->first();
    }

    public function getSkuCodeGen($id, $lastCode = 0)
    {

        $code = $id * 1000000 + $lastCode + 1;

        while ($this->where('code_sku', "SKU-$code")->count() > 0) {
            $code += 1;
        }

        return "SKU-$code";
    }

    public function populate($request = [], BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        $req = new Request($request);
        $model->organization_id = (int)AuthToken::info()->organizationId;

        $model->item_name = (string)$req->get('item_name');

        //TODO: jika slug sudah ada maka sistem generate alternatif slug
        $model->slug = !empty($req->get('slug'))
            ? strtolower(str_slug($req->get('slug')))
            : strtolower(str_slug($req->get('item_name')));

        $model->description = (string)$req->get('description');

        $model->barcode = (string)$req->get('barcode');
        $model->code_sku = $req->get('code_sku') ?? NULL;

        $model->weight = parseToGram(
            $req->get('weight') ?? 1,
            $req->get('weight_unit') ?? 'gr'
        );
        $model->weight_unit = (string)($req->get('weight_unit') ?? 'gr');

        $model->dimension_l = (int)$req->get('dimension_l');
        $model->dimension_w = (int)$req->get('dimension_w');
        $model->dimension_h = (int)$req->get('dimension_h');

        $model->item_attributes = strtolower(json_encode($req->get('item_attributes')));

        $model->sales_checked = 1;
        $model->sales_rate = (float)$req->get('sales_rate');
        $model->compare_rate = (float)$req->get('compare_rate');

        $model->purchase_checked = 1;

        $model->inventory_checked = 1;
        $model->inventory_stock_warning = (int)$req->get('inventory_stock_warning');
        $model->track_inventory = AuthToken::isInventory() ? (strtolower($req->get('track_inventory')) === 'true' || $req->get('track_inventory') === true ? true : false) : false;

        $model->parent_id = intOrNull($req->get('parent_id'));

        $model->uom_id = intOrNull($req->get('uom_id'));
        $model->tax_id = intOrNull($req->get('tax_id'));
        $model->category_id = intOrNull($req->get('category_id'));

        $model->item_status = (boolean)$req->get('item_status') ?? true;
        $model->is_shown_in_shop = $req->get('is_shown_in_shop');
//        $model->visibility = strtolower(json_encode($req->get('visibility')));

        $model->tags = (string)$req->get('tags');
        $model->page_title = (string)$req->get('page_title');
        $model->meta_description = (string)$req->get('meta_description');

//        $model->inventory_stock = (int)$req->get('inventory_stock');
//        $model->sales_account = $req->get('sales_account');
//        $model->sales_description = $req->get('sales_description');
//        $model->purchase_rate = $req->get('purchase_rate');
//        $model->purchase_account = $req->get('purchase_account');
//        $model->purchase_description = $req->get('purchase_description');
//        $model->inventory_rate = $req->get('sales_rate');/*$req->get('inventory_rate');*/
//        $model->inventory_account = $req->get('inventory_account');

        return $model;
    }

    public function checkSlug($slugName){
        $slugCondition = true;
        $slugNumber = 1;
        $slugNameNew = $slugName;
        while($slugCondition){
            $slugDB = DB::table($this->table)->select('slug')->where('slug', 'like', $slugNameNew)->get();
            if(count($slugDB) > 0){
                if($slugNameNew === $slugDB[0]->slug){
                    $slugNameNew = $slugName.'-'.$slugNumber;
                    $slugNumber++;
                }
            }
            else{
                $slugCondition = false;
            }
        }
        return $slugNameNew;
    }

    /**
     * @param $media
     * @param null $itemId
     * @return array|null
     * @throws Exception
     */
    public function uploadMediaAndPop($media, $itemId = null): array
    {
        try {
            //validate media variable if has data
            if (!isset($media['data'])) {
                throw new AppException(
                    "invalid image format",
                    Response::HTTP_BAD_REQUEST);
            }

            //get valid data type
            $mediaType = MediaUtil::getType($media['data']);

            //for file base64
            if ($mediaType == 'file') {

                /*proses upload ke aws*/
                $medRes = Image::inst()
                    ->base64Upload($media['data'], 'items');

                if ($medRes['response']['@metadata']['statusCode'] != 200) {
                    throw AppException::inst(
                        "Uploading image to server failed. Please try again later.",
                        Response::HTTP_BAD_REQUEST
                    );
                }

                /*mapping item media*/
                $medData = [
                    'media_type' => 'file',
                    'media_url' => env('S3_URL') . '/' . $medRes['request']['Key'],
                    'is_main' => false, // set default false
                    'item_media_status' => true,
                    'is_resized' => false
                ];

                return $medData;
            }

            //remove if has temp string in path
            $mediaUrl = Image::inst()->getRightPath($media['data']);

            //populate
            return [
                'media_type' => $mediaType,
                'media_url' => $mediaUrl,
                'is_main' => isset($media['is_main']) ? (boolean)$media['is_main'] : false,
                'item_id' => $itemId,
                'item_media_status' => true,
                'is_resized' => false
            ];

        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getStock(): ?Stock
    {
        $param = new StockKeyParam($this->item_id);
        return $this->getStockService()->detail($param);
    }

    private function setCurrentStock(int $newStockQty = 0)
    {
        //if parameter is empty, skip
        if (!$newStockQty) {
            return;
        }
        $oldStock = 0;

        //get current stock
        $dbStock = $this->getStock();

        //if current stock is the same as parameter, then skip
        if ($dbStock) {
            $oldStock = $dbStock->quantity;
            if ($newStockQty == $oldStock) {
                return;
            }
        }

        $adjustParam = new AdjustStockValue(
            $this->item_id,
            $newStockQty - $oldStock
        );

        $this->getStockService()->adjust($adjustParam);
    }

    /**
     * @param array $request
     * @return BaseModel|Item|MasterModel
     * @throws Exception
     */
    public function storeExec(array $request)
    {
        DB::beginTransaction();
        try {

            //populate item request
            $data = $this->populate($request);
            $data->slug = $this->checkSlug($data->slug);

            //save item
            if (!$data->save()) {
                Log::error(json_encode($data->errors));
                if($data->errors[0] === 'The slug must be unique in your organization'){
                    throw new AppException('Failed to save item.',
                        Response::HTTP_BAD_REQUEST, [trans('messages.item_name_used')]);
                }
                else{
                    throw new AppException('Failed to save item.',
                        Response::HTTP_BAD_REQUEST, $data->errors);
                }
            }

            //update skuCode if request is empty
            if (!isset($request['code_sku']) || empty($request['code_sku']))
                $data->update(['code_sku' => $this->getSkuCodeGen($data->item_id)]);

            #save images, if param image is available
            if (isset($request['images']) && !empty($request['images'])) {
                $mediaPop = array_map(function ($med) use ($data) {
//                    $med = $this->uploadMediaAndPop($med, $data->item_id);

                    $itemMedia = ItemMedia::inst()
                        ->storeMediaInSpecificItem(
                            $this->uploadMediaAndPop($med, $data->item_id),
                            $data->item_id
                        );

                    //item search availability main image media in item
//                    $itemMedia = ItemMedia::inst();
//                    if ($itemMedia->isMainInItemAvailable($data->item_id))
//                        $med['is_main'] = true;
//
//                    $itemMedia->where('item_id', $data->item_id);
//
//                    $imageData = $itemMedia->populate($med);
//                    if (!$imageData->save())
//                        throw AppException::flash(
//                            Response::HTTP_UNPROCESSABLE_ENTITY,
//                            "Save image failed."
//                        );

                    return $itemMedia->media_url;
                }, $request['images']);
            }

            //use stock_quantity parameter to call setCurrentStock
            if ((!isset($request['children'])
                    || empty($request['children']))
                && ($request['track_inventory'] === 'true'
                    || $request['track_inventory'] === true)) {

                $data->setCurrentStock(
                    isset($request['stock_quantity'])
                        ? $request['stock_quantity']
                        : 0
                );
            }

            #save children
            if (isset($request['children']) && !empty($request['children'])) {
                array_map(function ($childReq, $index) use ($data) {
                    $childReq['parent_id'] = $data->item_id;
                    $childReq['track_inventory'] = $data->track_inventory;
                    $childData = $this->populate($childReq);

                    if (!$childData->save()) {
                        DB::rollback();
                        Log::error(json_encode($data->errors));
                        throw AppException::inst(
                            'Save variant failed.',
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            $childData->errors
                        );
                    }

                    //set sku code if it empty request
                    if (empty($childReq['code_sku']))
                        $childData->update(['code_sku' => $this->getSkuCodeGen($data->item_id, $index)]);

                    if ($data->track_inventory) {
                        $childData->setCurrentStock(isset($childReq['stock_quantity']) ?
                            $childReq['stock_quantity'] : 0);
                    }

                    return $childData;
                }, $request['children'],
                    array_keys($request['children']));
            }

            //commit transaction
            DB::commit();

            //move to the right path aws / remove temp
            if (!empty($mediaPop))
                Image::inst()
                    ->copyBulk(array_map(function ($obj) {
                        return $obj['data'];
                    }, $request['images']));

            return $data;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @param array $request
     * @param int $id
     * @return BaseModel|Item|MasterModel
     * @throws Exception
     */
    public function updateExec(array $request, int $id)
    {
        DB::beginTransaction();
        try {

            $item = $this->getByIdInOrgRef($id)->with('children')->firstOrFail();

            $data = $this->populate($request, $item);

            if (!$data->save()) {
                DB::rollback();
                if($data->errors[0] === 'The slug must be unique in your organization'){
                    throw AppException::inst('Updating item contains errors.', Response::HTTP_BAD_REQUEST, [trans('messages.item_name_used')]);
                }
                else{
                    throw AppException::inst('Updating item contains errors.', Response::HTTP_BAD_REQUEST, $data->errors);
                }
            }

            if (!$item->children->isEmpty()) {
                $childrenRes = $item->children->map(function ($child) use ($data) {
                    $child->item_name = $data->item_name;
                    $child->description = $data->description;

                    $child->uom_id = $data->uom_id;
                    $child->weight = $data->weight;
                    $child->weight_unit = $data->weight_unit;
                    $child->dimension_l = $data->dimension_l;
                    $child->dimension_w = $data->dimension_w;
                    $child->dimension_h = $data->dimension_h;

                    $child->category_id = $data->category_id;
                    $child->tags = $data->tags;
                    $child->item_status = $data->item_status;

                    $child->page_title = $data->page_title;
                    $child->meta_description = $data->meta_description;
                    $child->slug = empty($child->slug) ? $child->slug : str_slug($child->item_name);
                    $child->is_shown_in_shop = $data->is_shown_in_shop;
//                    $child->visibility = $data->visibility;
                    if (!$child->save())
                        Log::error(json_encode('update child failed. cause' . $child->errors));

                    return $child;
                });

                if ($childrenRes && $childrenRes->pluck('errors')->filter()->isEmpty()) {
                    DB::rollback();
                    Log::error("Update child item is errors." . $childrenRes->pluck('errors')->filter());
                    throw AppException::inst('Update child item is errors.', Response::HTTP_BAD_REQUEST, $childrenRes->pluck('errors')->filter());
                }
            }

            if ($request['track_inventory'] === 'true' || $request['track_inventory'] === true) {
                //use stock_quantity parameter to call setCurrentStock
                $data->setCurrentStock(isset($request['stock_quantity']) ?
                    $request['stock_quantity'] : 0);
            }

            DB::commit();
            return $data;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @param $file_url
     * @param array $authInfo
     * @return mixed
     * @throws Exception
     */
    public function importMass($file_url, array $authInfo): void
    {
        try {
            AuthToken::setInfo($authInfo);

            $name = 'tmp/' . Carbon::now()->toAtomString() . '.xls';
            Storage::put($name, Storage::disk('s3')->get($file_url));

            //load excel file
            $reader = Excel::selectSheets('main')
                ->load(storage_path('app/' . $name),
                    function ($reader) {
                        return $reader;
                    })
                ->get();

            if (empty($reader->count())) {
                Log::error("Main Sheet does not exist.");
                throw AppException::flash(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    "Main sheet does not exist."
                );
            }

            //initialize result report
            $results = [
                'total' => $reader->count(),
                'failure' => 0,
                'success' => 0,
                'result' => []
            ];

            foreach ($reader->toArray() as $k => $row) {
                //initialize
                $error = [];
                $data = null;

                $validator = Validator::make($row, [
                    'name' => 'required|string',
                    'price' => 'required|numeric|between:0,9999999999',
                    'weight' => 'required|integer',
                    'length' => 'required|integer',
                    'width' => 'required|integer',
                    'height' => 'required|integer',
                    'track_stock' => 'required|boolean',
                    'uom' => 'required|String',
                    'tax' => 'required|String',
                    'related_sku' => 'nullable|string|exists:items,code_sku'
                ]);

                if ($validator->fails()) {
                    Log::error("Invalidate param " . json_encode($validator->getMessageBag()->all()));
                    array_push($error,
                        $validator->getMessageBag()->all()
                    );
                } else {

                    if ($row['related_sku']) {
                        $parent_id = $this->getBySkuCode($row['related_sku'])->item_id;
                    }

                    $uom_id = AssetUom::getByNameInOrg($row['uom'])->uom_id;
                    $tax_id = AssetTax::getByNameInOrg($row['tax'])->tax_id;

                    $mappedData = [
                        'organization_id' => AuthToken::$info->organizationId,
                        'item_name' => $row['name'],
                        'description' => $row['description'] ?? "",
                        'barcode' => (string)$row['barcode'] ?? "",
                        'code_sku' => $row['sku'] ?? "",
                        'weight' => $row['weight'],
                        'weight_unit' => 'gr',
                        'dimension_l' => $row['length'],
                        'dimension_w' => $row['width'],
                        'dimension_h' => $row['height'],
                        'uom_id' => $uom_id,
                        'tax_id' => $tax_id,
                        'sales_rate' => $row['price'],
                        'compare_rate' => $row['compare_price'],
                        'track_inventory' => $row['track_stock'],
                        'stock_quantity' => $row['stock_quantity'],
                        'parent_id' => $parent_id,
                        'images' => empty($row['image_url'])
                            ? null
                            : ['data' => $row['image_url'],
                                'is_main' => true],
                        'tags' => $row['tags'],
                        'page_title' => $row['page_title'] ?? "",
                        'meta_description' => $row['meta_description'] ?? ""
                    ];

                    Log::info('store item ' . $mappedData['item_name']);

                    $data = $this->populate($mappedData);

                    if (!$data->save()) {
                        //push error result email
                        array_push($error, $data->errors->all());
                    }

                    //add skuCode if empty request
                    if (empty($mappedData['code_sku']))
                        $data->update(['code_sku' => $this->getSkuCodeGen($data->item_id)]);

                    //save stock
                    if ($data->track_inventory) {
                        $data->setCurrentStock(
                            isset($mappedData['stock_quantity'])
                                ? $mappedData['stock_quantity'] : 0
                        );
                    }

                    #save images
                    if (isset($mappedData['images'])) {
                        $med = [
                            'media_type' => MediaUtil::getType($mappedData['images']['data']) ?? "url",
                            'media_url' => $mappedData['images']['data'],
                            'is_main' => $mappedData['images']['is_main'],
                            'item_media_status' => true,
                            'is_resized' => false,
                            'item_id' => $data->item_id
                        ];

                        # jika is_main tidak ditemukan di param, maka set false
                        if (!isset($med['is_main']))
                            $med['is_main'] = false;

                        $imageData = ItemMedia::inst()->populate($med);
                        if (!$imageData->save()) {
                            //push error result email
                            array_push($error, $imageData->errors->all());
                        }
                    }
                }

                //calculate
                if (!empty($error)) {
                    array_push($results['result'], [
                        'item_name' => $data->item_name ?: $row['name'],
                        'messages' => $error
                    ]);
                    Log::debug('item ' . $data->item_name ?: $row['name'] . " -- " . json_encode($error));
                    $results['failure']++;
                } else {
                    $results['success']++;
                }
            }

            Log::info("
            Import: {$results['total']}, 
            Success: {$results['success']}, 
            Failure: {$results['failure']},
            ORG " . AuthToken::info()->organizationName
                . ', PIC ' . AuthToken::info()->email . ' IS DONE.');

            //delete temporary
            Storage::delete($name);

            Mail::to(AuthToken::info()->email)
                ->send(ReportImportMassMail::inst(
                    AuthToken::info(),
                    $results
                ));

        } catch (Exception $e) {
            Storage::delete($name);
            Log::error("FAILED TO IMPORT ITEM MASS IN ORG " . AuthToken::info()->organizationName);

            Mail::to(AuthToken::info()->email)
                ->send(ReportImportMassMail::inst(
                    AuthToken::info(),
                    ['message' => $e->getMessage()],
                    ReportImportMassMail::STATUS_EXCEPTION
                ));

            throw $e;
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return BaseModel|ItemMedia|MasterModel
     * @throws Exception
     */
    public function addImage($id, Request $request)
    {
        DB::beginTransaction();

        try {
            $populatedMedia = $this->uploadMediaAndPop($request->input());

            $existingItem = $this
                ->getByIdInOrgRef($id)
                ->with('item_medias')
                ->firstOrFail();

            $populatedMedia['item_id'] = $existingItem->item_id;

            # jika is_main tidak ditemukan di param, maka set true yang lain set false
            if ($existingItem->item_medias->isEmpty())
                $populatedMedia['is_main'] = true;
            else
                $populatedMedia['is_main'] = false;

            $imageData = ItemMedia::inst()
                ->populate($populatedMedia);

            if (!$imageData->save()) {
                DB::rollBack();
                return $this->json(
                    Response::HTTP_BAD_REQUEST,
                    "Some Image does not saved, it will be rollback.",
                    $imageData->errors
                );
            }

            Image::inst()->copyBulk([
                $imageData->media_url
            ]);

            DB::commit();
//            Image::inst()->removeTemp();

            return $imageData;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param $id
     * @param $imageId
     * @return mixed
     * @throws Exception
     */
    public function setPrimaryImage($id, $imageId)
    {
        DB::beginTransaction();

        try {

            $existingMedias = $this
                ->getByIdInOrgRef($id)
                ->with('item_medias')
                ->firstOrFail();

            $result = $existingMedias->item_medias
                ->map(function ($i) use ($imageId) {
                    $i['is_main'] = $i->item_media_id == $imageId ?: false;

                    if (!$i->save()) {
                        DB::rollback();
                        throw AppException::flash(
                            Response::HTTP_INTERNAL_SERVER_ERROR,
                            trans('messages.set_as_primary_image_failed')
                        );
                    }
                    return $i;
                });

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @param $id
     * @param $imageId
     * @return mixed
     * @throws Exception
     */
    public function removeImage($id, $imageId)
    {
        DB::beginTransaction();
        try {

            if ($existingItemMedia = ItemMedia::getByItemAndIdRef($id, $imageId)->firstOrFail())
                $existingItemMedia->delete();

            if (!ItemMedia::inst()->isMainInItemAvailable($id)) {
                $firstItemMedia = ItemMedia::getByItem($id)->first();
                if (!$firstItemMedia->update(['is_main' => true])) {
                    throw AppException::inst(
                        "Delete image failed."
                    );
                }
            }

            DB::commit();

            //remove from aws
            if ($existingItemMedia->media_type == 'file') {
                Image::inst()->removeObject(
                    $existingItemMedia->media_url,
                    false
                );
            }

            return $existingItemMedia;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

}
