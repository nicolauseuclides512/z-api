<?php

namespace App\Http\Controllers;

use App\Cores\Variable;
use App\Exceptions\AppException;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\PatternController;
use App\Http\Controllers\Base\RestFulControl;
use App\Jobs\Item\ImportMassItemJob;
use App\Models\AssetAccount;
use App\Models\AssetAttribute;
use App\Models\AssetCategory;
use App\Models\AssetTax;
use App\Models\AssetUom;
use App\Models\AuthToken;
use App\Models\Image;
use App\Models\Item;
use App\Models\ItemMedia;
use App\Models\Setting;
use App\Transformers\ItemTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Description of ItemController
 * APIs for Items module
 * @author Jehan Afwazi A <jehan@ontelstudio.com>
 */
class ItemController extends BaseController implements PatternController
{
    use RestFulControl;

    public $configName = "Item";

    public $statusField = "item_status";

    public $requiredFilter = [
        "all",
        "active",
        "inactive",
        "low_stock",
        "un_group",
        "sales",
        "inventory",
        "purchase"
    ];

    protected $sortBy = [
        "created_at",
        "item_name",
        "code_sku",
        "barcode",
        "sales_rate",
        "weight",
        "item_status",
        "inventory_stock"
    ];

    public $requiredParamFetch = [];

    public $requiredParamStore = [
        "uom_id",
        "item_name",
        "weight"
    ];

    public $requiredParamMark = [
        'active',
        'inactive'
    ];

    public function __construct(
        Request $request,
        ItemTransformer $itemTransformer
    )
    {
        parent::__construct(
            Item::inst(),
            $request,
            true);
        $this->transformer = $itemTransformer;
    }

    /**
     * @return array
     * @throws AppException
     */
    public function _resource()
    {
        $loginInfo = $this->model->getLoginInfo();

        $weight_units = Variable::WEIGHTS;
        $default_weight_unit = Setting::findByKeyInOrg('global.unit.weight')->value;

        $attributes = AssetAttribute::inst()->setLoginInfo($loginInfo)->getInOrgRef()->cast()->get();
        $uom = AssetUom::inst()->setLoginInfo($loginInfo)->getInOrgRef()->cast()->get();
        $account = AssetAccount::inst()->setLoginInfo($loginInfo)->getInOrgRef()->cast()->get();
        $tax = AssetTax::inst()->setLoginInfo($loginInfo)->getInOrgRef()->cast()->get();
        $category = AssetCategory::inst()->setLoginInfo($loginInfo)->getInOrgRef()->cast()->get();

        //TODO (jee) : sementara .. validasi
        $url = "http://" . AuthToken::info()->organizationPortal . "." . env('APP_SUB_DOMAIN') . "/items/";

        return [
            "uoms" => $uom,
            "accounts" => $account,
            "taxes" => $tax,
            "attributes" => $attributes,
            "categories" => $category,
            "weight_units" => $weight_units,
            "default_weight_unit" => $default_weight_unit,
            "url" => $url,
        ];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param Request $request
     */
    public function fetch()
    {
        try {
            $data = $this->model;

            if ($this->useNestedOnList) {
                $data = $data->nested();
            }

            $data = $data->filter(
                $this->requestMod()['filter_by'],
                $this->requestMod()['q'],
                $this->request->get("leaf_only") === 'true' ? true : false)
                ->orderBy(
                    $this->requestMod()['sort_column'],
                    $this->requestMod()['sort_order'])
                ->paginate($this->request->input("per_page"));

            if (!$data) {
                Log::error($this->configName . " Not Found");
                throw AppException::flash(
                    Response::HTTP_BAD_REQUEST,
                    $this->configName . " Not Found"
                );
            }

            $fields = !empty($this->request->get('fields'))
                ? explode(',', preg_replace(
                        '/\s+/',
                        '',
                        $this->request->get('fields'))
                ) : [];

            $items = $this
                ->transformer
                ->showFields($fields ?: ItemTransformer::SIMPLE_FIELDS)
                ->includeRelations($fields ?: ['children'])
                ->createCollectionPageable($data);

            return $this->json(
                Response::HTTP_OK,
                $this->configName . " fetched.",
                $items);

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }

    }

    public function edit($id = null)
    {
        $item = $this->getModel()
            ->getByIdInOrgRef($id)
            ->nested()
            ->child($column = ["*"])
            ->first();

        if ($item) {
            $resource = $this->_resource();
            $resource['item'] = $item;

            return $this->json(
                Response::HTTP_OK,
                "fetch edit data",
                $resource
            );
        }
        return $this->json(
            Response::HTTP_BAD_REQUEST,
            "Item not found."
        );
    }

    public function detail($id = null)
    {
        try {
            $data = $this->getModel()
                ->getByIdInOrgRef($id)
                ->nested()
                ->child(["*"])
                ->cast()
                ->firstOrFail();

            if (!empty($data)) {
                return $this->json(
                    Response::HTTP_OK,
                    "get " . $this->configName . " by id " . $id,
                    $data
                );
            }

            return $this->json(
                Response::HTTP_BAD_REQUEST,
                $this->configName . " with id " . $id . " not found");

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function store(Request $request)
    {
        try {

            $data = $this
                ->model
                ->storeExec($request->all());

            return $this->json(
                Response::HTTP_CREATED,
                trans("messages.item_saved"),
                $data
            );

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function update($id = null, Request $request)
    {
        try {
            $data = $this
                ->model
                ->updateExec($request->all(), $id);

            return $this->json(
                Response::HTTP_CREATED,
                trans('messages.item_updated'),
                $data
            );

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }


    /**
     * TODO (jee) : trigger delete file in aws blm terhandle
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Request $request)
    {
        try {
            $input = $request->get('ids');

            if (!$input) {
                throw AppException::inst(
                    "param ids not found",
                    Response::HTTP_BAD_REQUEST
                );
            }

            $ids = explode(',',
                preg_replace('/\s+/',
                    '',
                    $input)
            );

            DB::beginTransaction();
            foreach ($ids as $id) {

                $data = $this->model->find($id);

                if (!$data) {
                    DB::rollback();
                    Log::error($this->configName . " with id " . $id . " doesn't exist");

                    throw AppException::inst(
                        $this->configName . " id " . $id . " in your Organisation doesn't exist",
                        Response::HTTP_UNPROCESSABLE_ENTITY
                    );
                }

                if (!$data->forceDelete()) {
                    DB::rollback();
                    Log::error($this->configName . " with id " . $id . "cannot be deleted");
                    throw AppException::inst(
                        "delete item failed.",
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        ["$data->name delete item failed."]
                    );
                }
            }

            DB::commit();
            Log::info($this->configName . " ids " . json_encode($ids) . " successfully deleted");

            return $this->json(
                Response::HTTP_OK,
                trans('messages.item_deleted')
            );

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    /**
     * tanpa harga grosir
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updatePrice($id, Request $request)
    {
        DB::beginTransaction();

        $newPrice = $request->input('new_price');
        $item = $this
            ->getModel()
            ->getByIdInOrgRef($id)
            ->first();

        $children = $item->children;

        if ($item) {
            $item->sales_rate = $newPrice;
            if (!$item->save()) {
                return $this->json(
                    Response::HTTP_BAD_REQUEST,
                    'set new price is failed . ',
                    $item
                );
            }

            if ($children) {
                $childrenRes = $children->map(
                    function ($child) use ($newPrice) {
                        $child->sales_rate = $newPrice;
                        $child->save();
                        return $child;
                    }, $children);

                if (!$childrenRes && !empty(array_column($childrenRes, "errors"))) {
                    DB::rollBack();
                    return $this->json(
                        Response::HTTP_BAD_REQUEST,
                        "Some " . $this->configName . " has not be set new price, we will rollback",
                        $childrenRes
                    );
                }
            }
            DB::commit();

            $successMsg = "Price updated";
            return $this->json(
                Response::HTTP_CREATED, $successMsg,
                $this->getModel()
                    ->getByIdInOrgRef($id)
                    ->child($column = ["*"])
                    ->first());
        }
        return $this->json(
            Response::HTTP_BAD_REQUEST,
            'item not found . '
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUploadCredential()
    {
        $cred = Image::inst()
            ->setLoginInfo($this->getModel()->getLoginInfo())
            ->getCredential('items');

        return $this->json(
            Response::HTTP_OK,
            "get upload credential items.",
            $cred);
    }

    #ATTRIBUTES

    /**
     * TODO (jehan) : disini perlu ada penambahan update parent
     * @param null $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAttribute($id = null, Request $request)
    {
        try {
            DB::beginTransaction();

            $key = $request->get("key");
            $val = $request->get("val");

            $data = $this->model->getByIdInOrgRef($id)->first();

            if (!empty($data)) {
                //tambahkan pada parrent
                $attrs = (object)$data->item_attributes;

                $attrs->{$key} = [$val];

                //parse trus disave
                $data->item_attributes = json_encode($attrs);

                if ($data->save()) {
                    //looping child terus tambah semua
                    $upChild = $data->children->map(
                        function ($child) use ($key, $val) {
                            $childAttrs = $child->item_attributes;
                            $childAttrs->{$key} = [$val];
                            $child->item_attributes = json_encode($childAttrs);

                            if (!$child->save()) {
                                Log::error("Can't add attribute in this child");
                            }
                            return $child;
                        })->toArray();

                    if (empty(array_column($upChild, "errors"))) {
                        DB::commit();
                        return $this->json(
                            Response::HTTP_CREATED,
                            $this->configName . " update item attribute is successfully",
                            $this->getModel()
                                ->getByIdInOrgRef($data->item_id)
                                ->child($column = [" * "])
                                ->first());
                    }
                    DB::rollback();

                    return $this->json(
                        Response::HTTP_BAD_REQUEST,
                        "Can't add attribute",
                        $upChild
                    );
                }

                return $this->json(
                    Response::HTTP_BAD_REQUEST,
                    "Can't add attribute",
                    $data
                );
            }

            return $this->json(
                Response::HTTP_BAD_REQUEST,
                "Item not found . "
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonExceptions($e);
        }
    }

    /**
     * @param null $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAttributeKey($id = null, Request $request)
    {
        try {
            DB::beginTransaction();

            $oldKey = $request->get("oldKey");
            $newKey = $request->get("newKey");

            $data = $this->getModel()
                ->getByIdInOrgRef($id)
                ->first();

            if (empty($data)) {
                return $this->json(
                    Response::HTTP_BAD_REQUEST,
                    "Item not found . "
                );
            }

            $attrs = $data->item_attributes;

            //replacer oldKey with newKey
            $replaceKey = function ($attrs, $old, $new) {
                $attrs->{$new} = $attrs->{$old};
                unset($attrs->{$old});
                return $attrs;
            };

            //periksa apakah oldkey itu ada di data
            if (!array_key_exists($oldKey, $attrs)) {
                return $this->json(
                    Response::HTTP_BAD_REQUEST,
                    $oldKey . " doesn't exist . "
                );
            }

            //set attribute field with replacer
            $data->item_attributes = json_encode($replaceKey($attrs, $oldKey, $newKey));

            //save data
            if (!$data->save()) {
                DB::rollback();
                return $this->json(
                    Response::HTTP_BAD_REQUEST,
                    "Update attribute key is failed.",
                    $data
                );
            }

            //looping children buat ngeset key name yang baru
            $upChild = $data->children->map(
                function ($child) use ($replaceKey, $oldKey, $newKey) {
                    $childAttrs = $child->item_attributes;
                    if (!empty($childAttrs) && array_key_exists($oldKey, $childAttrs)) {
                        //set attribute field with replacer
                        $child->item_attributes = json_encode($replaceKey($childAttrs, $oldKey, $newKey));
                        //save child
                        if (!$child->save())
                            Log::error("can't update attribute, caused " . $child['errors']);
                    }
                    return $child;
                })->toArray();

            //periksa apakah ada error?if not commit transaction
            if (!empty(array_column($upChild, "errors"))) {
                DB::rollback();
                return $this->json(
                    Response::HTTP_BAD_REQUEST,
                    "Update attribute key is failed.",
                    $upChild
                );
            }

            //commit process success.
            DB::commit();
            return $this->json(
                Response::HTTP_CREATED,
                $this->configName . " update item attribute is successfully",
                $this->getModel()
                    ->getByIdInOrgRef($data->item_id)
                    ->child($column = ["*"])
                    ->first()
            );


        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonExceptions($e);
        }
    }

    /**
     * @param null $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroyAttributeVal($id = null, Request $request)
    {
        try {
            //molai transaksi
            DB::beginTransaction();

            $key = $request->get("key");
            $val = $request->get("val");

            $data = $this->model
                ->getByIdInOrgRef($id)
                ->firstOrFail();

            if (empty($data)) {
                return $this->json(
                    Response::HTTP_BAD_REQUEST,
                    "Item not found . "
                );
            }

            $attrs = $data->item_attributes;
            //check key or value exist
            if (array_key_exists($key, $attrs) && in_array($val, $attrs->{$key})) {
                //get total array attribute dari input key
                $parentAttrSize = sizeof($attrs->{$key});

                //jika total = 1 maka langsung hapus array key
                //jika tidak maka hapus value dari request input
                if ($parentAttrSize === 1)
                    unset($attrs->{$key});
                else {
                    unset($attrs->{$key}[array_search($val, $attrs->{$key})]);
                    $attrs->{$key} = array_values($attrs->{$key});
                }

                //memasukkan data attribute yang baru
                $data->item_attributes = json_encode($attrs);

                /*save data*/
                if ($data->save()) {
                    //loop children
                    $upChild = $data->children->map(
                        function ($child) use ($key, $val, $parentAttrSize) {
                            $childAttrs = $child->item_attributes;
                            if (!empty($childAttrs) && in_array($val, $child->item_attributes->{$key})) {
                                //jika parent size = 1, hapus key sesuai request kmdian update item
                                //jika tidak hapus item
                                if ($parentAttrSize === 1) {
                                    unset($childAttrs->{$key});
                                    $child->item_attributes = json_encode($childAttrs);
                                    if (!$child->save())
                                        Log::error("Can't update attribute on child caused " . $child["errors"]);
                                } else {
                                    if (!$child->delete())
                                        Log::error("Can't delete child caused " . $child["errors"]);
                                }
                            }
                            return $child;
                        })->toArray();
                    //memeriksa apakah tidak ada error saat hapus atau update item?
                    //if not commit transaksi, if yes rollback
                    if (empty(array_column($upChild, "errors"))) {
                        DB::commit();
                        return $this->json(
                            Response::HTTP_OK,
                            $this->configName . " delete by item attribute is successfully",
                            $this->getModel()
                                ->getByIdInOrgRef($data->item_id)
                                ->child($column = [" * "])
                                ->first());
                    }
                    DB::rollback();
                    return $this->json(
                        Response::HTTP_BAD_REQUEST,
                        "Delete child by attribute item is failed",
                        $upChild
                    );
                }
                return $this->json(
                    Response::HTTP_BAD_REQUEST,
                    "Delete attribute item is failed",
                    $data
                );
            }

            return $this->json(
                Response::HTTP_BAD_REQUEST,
                "Please check your input request key or value . "
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonExceptions($e);
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addImage($id, Request $request)
    {
        try {
//            $validator = Validator::make($request->input(), [
////                'is_main' => 'required|boolean',
//                'data' => 'required|base64_image:jpeg,png',
//            ]);
//
//            if ($validator->fails()) {
//                throw AppException::inst(
//                    trans('messages.invalid_image_upload'),
//                    Response::HTTP_UNPROCESSABLE_ENTITY,
//                    $validator->errors()
//                );
//            }

            return $this->json(
                Response::HTTP_CREATED,
                trans('messages.image_added'),
                $this->model->addImage($id, $request)
            );

        } catch (Exception $e) {
            DB::rollBack();
            return $this->jsonExceptions($e);
        }
    }

    /**
     * @param $itemId
     * @param $imageId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeImage($itemId, $imageId)
    {
        try {
            return $this->json(
                Response::HTTP_OK,
                trans('messages.image_deleted'),
                $this->model->removeImage($itemId, $imageId)
            );

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    /**
     * @param $itemId
     * @param $imageId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setPrimary($itemId, $imageId)
    {
        try {

            return $this->json(
                Response::HTTP_CREATED,
                trans('messages.set_as_primary_image_success'),
                $this->model->setPrimaryImage($itemId, $imageId)
            );

        } catch (Exception $e) {
            DB::rollback();
            return $this->jsonExceptions($e);
        }
    }

#END MEDIA

    public function importMass()
    {
        try {
            $job = (new ImportMassItemJob(
                $this->request->input('raw_file'),
                AuthToken::info()
            ));

            $this->dispatch($job);

            return $this->json(
                Response::HTTP_OK,
                "Import item is processed. Check your email to see the report."
            );

        } catch (Exception $e) {
            return $this->jsonExceptions($e);
        }
    }
}

