<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\PatternController;
use App\Http\Controllers\Base\RestFulControl;
use App\Models\AssetPaymentTerm;
use App\Models\AuthToken;
use App\Models\Contact;
use App\Models\Setting;
use App\Services\Gateway\Base\BaseServiceContract;
use App\Services\Gateway\Rest\RestService;
use App\Transformers\ContactTransformer;
use App\Utils\StringUtil;
use Database\Utils\CsvConverter;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContactController extends BaseController implements PatternController
{
    use RestFulControl;

    public $configName = "contact";

    public $statusField = "contact_status";

    public $requiredFilter = [
        'all',
        'inactive',
        'active',
        'customer',
        'dropshipper',
        'vendor'];

    protected $sortBy = [
        "display_name",
        "company_name",
        "email",
        "phone",
        "created_at",
        "contact_id"];

    public $requiredParamStore = [
        "currency_id",
        "payment_term_id",
        "first_name",
        "display_name",
        "contact_status"];

    public $requiredParamMark = [
        'active',
        'inactive'];

    public function __construct(
        ContactTransformer $contactTransformer,
        Request $request,
        BaseServiceContract $service)
    {
        parent::__construct(
            $modelName = Contact::inst(),
            $request,
            $useAuth = true,
            $service);

        $this->transformer = $contactTransformer;
    }

    public function _resource()
    {
        $assetPaymentTerms = AssetPaymentTerm::inst()->getInOrgRef()->cast()->get();
        $currencySetting = Setting::findByKeyInOrg('global.currency.currency_id')->value;

        return array(
            "payment_term" => $assetPaymentTerms,
            "currency_setting_default" => $currencySetting,
        );
    }

    public function fetch()
    {
        try {
            $data = $this->model;

            if ($this->useNestedOnList) {
                $data = $data->nested();
            }

            $data = $data->filter(
                $this->requestMod()['filter_by'],
                $this->requestMod()['q']
            )->orderBy(
                $this->requestMod()['sort_column'],
                $this->requestMod()['sort_order']
            )->paginate(
                $this->request->input("per_page")
            );

            if (!$data) {
                Log::error($this->configName . " Not Found");
                return $this->json(Response::HTTP_BAD_REQUEST,
                    $this->configName . " Not Found", []);
            }

            $fields = !empty($this->request->get('fields'))
                ? explode(',', preg_replace(
                        '/\s+/',
                        '',
                        $this->request->get('fields'))
                ) : [];

            $contact = $this
                ->transformer
                ->showFields($fields ?? ContactTransformer::SIMPLE_FIELDS)
                ->createCollectionPageable($data);

            Log::info($this->configName . " fetched");

            return $this->json(Response::HTTP_OK,
                $this->configName . " fetched.", $contact);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function create()
    {
        return $this->json(
            Response::HTTP_OK,
            "Fetch Create",
            $this->_resource());
    }

    public function edit($id = null)
    {
        $contact = $this->getModel()->getByIdInOrgRef($id)->nested()->first();
        if ($contact) {
            $resource = $this->_resource();
            $resource['contact'] = $this->_popDetailRelation($contact);
            return $this->json(Response::HTTP_OK, "Fetch edit", $resource);
        }
        return $this->json(Response::HTTP_BAD_REQUEST, "contact not found.");
    }

    public function detail($id = null)
    {
        try {
            $data = $this->model
                ->getByIdInOrgRef($id)
                ->first();

            if (!$data) {
                Log::error($this->configName . " with id " . $id . " not found");
                return $this->json(
                    Response::HTTP_BAD_REQUEST,
                    $this->configName . " with id " . $id . " not found");
            }

            Log::info("Get " . $this->configName . " by id " . $id);

            if ($this->request->input("include_area") === 'true') {

                if (!empty($data->billing_country) ||
                    !empty($data->billing_province) ||
                    !empty($data->billing_district) ||
                    !empty($data->billing_region)) {
                    $billingArea = RestService::inst(
                        $this->service->getClient())
                        ->getArea(
                            $data->billing_country,
                            $data->billing_province,
                            $data->billing_district,
                            $data->billing_region
                        );

                    $data->billing_country_detail = $billingArea->country ?? null;
                    $data->billing_province_detail = $billingArea->province ?? null;
                    $data->billing_district_detail = $billingArea->district ?? null;
                    $data->billing_region_detail = $billingArea->region ?? null;
                }

                if (!empty($data->shipping_country) ||
                    !empty($data->shipping_province) ||
                    !empty($data->shipping_district) ||
                    !empty($data->shipping_region)) {
                    $shippingArea = RestService::inst(
                        $this->service->getClient())
                        ->getArea(
                            $data->shipping_country,
                            $data->shipping_province,
                            $data->shipping_district,
                            $data->shipping_region
                        );

                    $data->shipping_country_detail = $shippingArea->country ?? null;
                    $data->shipping_province_detail = $shippingArea->province ?? null;
                    $data->shipping_district_detail = $shippingArea->district ?? null;
                    $data->shipping_region_detail = $shippingArea->region ?? null;
                }
            }
            $contact = $this
                ->transformer
                ->createItem($data);

            return $this->json(
                Response::HTTP_OK,
                "get " . $this->configName . " by id " . $id,
                $contact
            );

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        } catch (\Throwable $e) {
        }
    }

    public function _popDetailRelation($contact)
    {
        try {

            if (!empty($contact->billing_country) ||
                !empty($contact->billing_province) ||
                !empty($contact->billing_district) ||
                !empty($contact->billing_region)) {
                $billingArea = RestService::inst(
                    $this->service->getClient())
                    ->getArea(
                        $contact->billing_country,
                        $contact->billing_province,
                        $contact->billing_district,
                        $contact->billing_region
                    );

                $contact->billing_country_detail = $billingArea->country ?? null;
                $contact->billing_province_detail = $billingArea->province ?? null;
                $contact->billing_district_detail = $billingArea->district ?? null;
                $contact->billing_region_detail = $billingArea->region ?? null;
            }

            if (!empty($contact->shipping_country) ||
                !empty($contact->shipping_province) ||
                !empty($contact->shipping_district) ||
                !empty($contact->shipping_region)) {
                $shippingArea = RestService::inst(
                    $this->service->getClient())
                    ->getArea(
                        $contact->shipping_country,
                        $contact->shipping_province,
                        $contact->shipping_district,
                        $contact->shipping_region
                    );

                $contact->shipping_country_detail = $shippingArea->country ?? null;
                $contact->shipping_province_detail = $shippingArea->province ?? null;
                $contact->shipping_district_detail = $shippingArea->district ?? null;
                $contact->shipping_region_detail = $shippingArea->region ?? null;
            }

            return $contact;
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        } catch (\Throwable $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $input = $request->get('ids');

            if (empty($input)) {
                throw AppException::inst("param id not found", Response::HTTP_BAD_REQUEST);
            }

            $ids = explode(',', preg_replace('/\s+/', '', $input));

            $delDataList = array_map(function ($id) {
                $data = $this->model->getByIdInOrgRef($id)->first();

                if (!empty($data)) {
                    if ($data->forceDelete()) {
                        Log::info($this->configName . " with id " . $id . " successfully deleted");
                        return ["id" => $id, 'message' => $this->configName . " with id " . $id . " successfully deleted"];
                    }
                    Log::error($this->configName . " with id " . $id . "cannot be deleted");
                    return ["id" => -1, "message" => $data['errors']];
                }
                Log::error($this->configName . " with id " . $id . " doesn't exist");
                return ["id" => -1, "message" => $this->configName . " id " . $id . " in this Organisation doesn't exist"];

            }, $ids);

            $successMsg = trans('messages.contact_deleted');

            if (!in_array(-1, array_column($delDataList, 'id'))) {
                return $this->json(Response::HTTP_OK, $successMsg, $delDataList);
            }
            return $this->json(Response::HTTP_BAD_REQUEST, $this->configName . " doesn't exist");
        } catch (\Exception $e) {
            Log::error(json_encode($e));
            return $this->jsonExceptions($e);
        }
    }

    public function importData(Request $request)
    {

        ini_set('max_execution_time', 600);

        DB::beginTransaction();

        $fileUrl = $request->get('raw_file');

        $uriSegments = explode("/", parse_url($fileUrl, PHP_URL_PATH));
        array_pop($uriSegments);


        try {

            Log::info('start import contact');

            if (empty($fileUrl)) {
                throw AppException::inst("File does not exist.", Response::HTTP_BAD_REQUEST);
            }

            $ext = pathinfo($fileUrl, PATHINFO_EXTENSION);

            if ($ext !== 'csv') {
                throw AppException::inst("The format file is not supported", Response::HTTP_BAD_REQUEST);
            }

            $retrievedData = Storage::disk('s3')->get($fileUrl);

            if (CsvConverter::detectDelimiter($retrievedData) !== ',') {
                throw AppException::inst("Delimiter must be comma (,)", Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $dataExtracted = CsvConverter::strToArray($retrievedData, ',');

            $i = 0;
            foreach ($dataExtracted as $data) {

                if (!isset($data['website']))
                    $data['website'] = "";

                if (!isset($data['phone']))
                    $data['phone'] = "";

                if (!isset($data['mobilephone']))
                    $data['mobilephone'] = "";

                if (!isset($data['emailid']) ||
                    Contact::inst()
                        ->where('email', $data['emailid'])
                        ->where('organization_id', AuthToken::info()->organizationId)
                        ->exists())
                    $data['emailid'] = null;

                $arrContact = [
                    'organization_id' => AuthToken::info()->organizationId,
                    'salutation_id' => $data['salutation_id'] ?? null,
                    'currency_id' => $data['currency_id'] ?? null,
                    'payment_term_id' => $data['payment_term_id'] ?? null,
                    'first_name' => !empty($data['first_name']) ? $data['first_name'] : str_random(10),
                    'last_name' => $data['last_name'] ?? null,
                    'display_name' => !empty($data['display_name']) ? $data['display_name'] : str_random(10),
                    'email' => $data['emailid'] ?? null,
                    'phone' => StringUtil::cleanPhone($data['phone']),
                    'mobile' => StringUtil::cleanPhone($data['mobilephone']),
                    'website' => filter_var($data['website'], FILTER_VALIDATE_URL) ? $data['website'] : null,
                    'company_title' => $data['company_title'] ?? null,
                    'company_name' => $data['company_name'] ?? null,
                    'display_code' => 1,
                    'billing_address' => $data['billing_address'] ?? null,
                    'billing_region' => $data['billing_region'] ?? null,
                    'billing_district' => $data['billing_district'] ?? null,
                    'billing_province' => $data['billing_province'] ?? null,
                    'billing_country' => $data['billing_country'] ?? null,
                    'billing_zip' => $data['billing_zip'] ?? null,
                    'billing_fax' => $data['billing_fax'] ?? null,
                    'shipping_address' => $data['shipping_address'] ?? null,
                    'shipping_region' => $data['shipping_region'] ?? null,
                    'shipping_district' => $data['shipping_district'] ?? null,
                    'shipping_province' => $data['shipping_province'] ?? null,
                    'shipping_country' => $data['shipping_country'] ?? null,
                    'shipping_zip' => $data['shipping_zip'] ?? null,
                    'shipping_fax' => $data['shipping_fax'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'contact_status' => $data['contact_status'] ?? true,
                    'is_customer' => $data['is_customer'] ?? false,
                    'is_vendor' => $data['is_vendor'] ?? false,
                    'is_reseller' => $data['is_reseller'] ?? false,
                    'is_dropshipper' => $data['is_dropshipper'] ?? false,
                ];

                //save contact
                $data = Contact::inst()->storeExec($arrContact);
                Log::info('Import contact #' . $i . ' : ' . $data->first_name);
                $i++;
            }

            //delete directory in s3
            Storage::disk('s3')->deleteDirectory(implode('/', $uriSegments));

            DB::commit();

            Log::info('import contact done.');
            ini_set('max_execution_time', 1000);
            return $this->json(Response::HTTP_CREATED, "Import data successfully.");
        } catch (\Exception $e) {
            //delete directory in s3

            Storage::disk('s3')->deleteDirectory(implode('/', $uriSegments));

            DB::rollback();
            ini_set('max_execution_time', 1000);
            return $this->jsonExceptions($e);
        }
    }

}
