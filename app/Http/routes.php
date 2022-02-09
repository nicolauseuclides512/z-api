<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It is a breeze. Simply tell Lumen the URIs it should respond to
  | and give it the Closure to call when that URI is requested.
  |
 */

use App\Mails\ReportImportMassTestMail;
use App\Utils\MailUtil;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

//Hack php version compatibility
if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
// Ignores notices and reports all other kinds... and warnings
    //   error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
}

include_once("routes/BaseRoutes.php");

$app->get('/', function () use ($app) {
    return 'Zuragan Store API ' . env('APP_VERSION') . ' [' . env('APP_ENV') . ']';
});

#API ROUTES
$app->group([
    'prefix' => "api/" . env("APP_VERSION", 'v1'),
    'middleware' => ['authToken', 'localization']],
    function ($app) {

        $app->get('/ping', function () {
            return 'pong';
        });

        $app->get('setup', ['uses' => 'InitController@setup']);

        #QUICK_REPLY_CATEGORIES
        rest('/quick_reply_category', 'QuickReplyCategoryController', $app);

        #QUICK_REPLIES
        rest('/quick_reply', 'QuickReplyController', $app);

        #SALES_CHANNELS
        $app->get('/sales-channels/list', ['uses' => 'SalesChannelController@list']);
        $app->get('/sales-channels/{id}/edit', ['uses' => 'SalesChannelController@edit']);
        $app->delete('/sales-channels/{id}', ['uses' => 'SalesChannelController@remove']);
        $app->post('/sales-channels', ['uses' => 'SalesChannelController@store']);
        $app->put('/sales-channels/{id}', ['uses' => 'SalesChannelController@update']);


        #ASSET_ACCOUNT
        rest('/accounts', 'AssetAccountController', $app);

        #ASSET_CATEGORIES
        rest('/categories', 'AssetCategoryController', $app);

        #ASSET_PAYMENT_TERM
        rest('/payment_terms', 'AssetPaymentTermController', $app);

        #ASSET_UOM
        $app->get('/uoms/{id}/set-default', ['uses' => 'AssetUomController@setDefault']);
        rest('/uoms', 'AssetUomController', $app);

        #ASSET_TAX
        rest('/taxes', 'AssetTaxController', $app);

        #ASSET_SALUTATION
        rest('/salutations', 'AssetSalutationController', $app);

        #ASSET_ATTRIBUTE
        rest('/attributes', 'AssetAttributeController', $app);

        #SETTING
        $app->get('/settings/edit', ['uses' => 'SettingController@edit']);
        $app->post('/settings/store_detail', ['uses' => 'SettingController@storeDetail']);
        $app->post('/settings/checkout', ['uses' => 'SettingController@setCheckout']);
        $app->post('/settings/shipping', ['uses' => 'SettingController@setShipping']);
        $app->post('/settings/tax', ['uses' => 'SettingController@setTaxes']);
        $app->post('/settings/payments', ['uses' => 'SettingController@addPaymentMethod']);
        $app->delete('/settings/payments', ['uses' => 'SettingController@destroyPaymentMethod']);
        $app->post('/settings/payments/{id}/add_detail', ['uses' => 'SettingController@addPaymentMethodDetail']);
        $app->delete('/settings/payments/remove_detail', ['uses' => 'SettingController@destroyPaymentMethodDetail']);
        $app->post('/settings/payments/bank_transfer/add', ['uses' => 'SettingController@addBankTransferPayment']);
        $app->delete('/settings/payments/bank_transfer/remove/{payment_id}', ['uses' => 'SettingController@removeBankTransferPayment']);

        #CONTACT
        rest('/contacts', 'ContactController', $app);
        $app->post('/contacts/import-data', 'ContactController@importData');

        #ITEM
        $app->group(['prefix' => 'items'], function ($app) {
            $app->get('/get_upload_credential', 'ItemController@getUploadCredential');
            rest('', 'ItemController', $app);
            $app->post('/import-mass', 'ItemController@importMass');
            $app->post('/{id}/attributes/delete', 'ItemController@destroyAttributeVal');
            $app->post('/{id}/attributes/update', 'ItemController@updateAttributeKey');
            $app->post('/{id}/attributes/add', 'ItemController@addAttribute');
            $app->post('/{id}/update_price', 'ItemController@updatePrice');
            $app->post('/{id}/images/add', 'ItemController@addImage');
            $app->delete('/{id}/images/remove/{medId}', 'ItemController@removeImage');
            $app->get('/{id}/images/set_primary/{imgId}', 'ItemController@setPrimary');
        });

        #COLLECTTION
        $app->get('/collections/get_upload_credential', 'ItemCollectionController@getUploadCredential');
        rest('/collections', 'ItemCollectionController', $app);
        $app->post('/collections/{id}/update_image', 'ItemCollectionController@updateImage');
        $app->get('/collections/{id}/items', 'ItemCollectionController@getItems');
        $app->delete('/collections/{id}/remove_image', 'ItemCollectionController@removeImage');

        $app->get('/discounts', 'DiscountController@fetch');
        $app->get('/discounts/create', 'DiscountController@create');
        $app->post('/discounts/{type}', 'DiscountController@store');
        $app->post('/discounts/mark_as/{status}', 'DiscountController@markAs');
        $app->delete('/discounts', 'DiscountController@destroy');

        #SALES ORDER
        $app->get('/sales_orders/get_credential', 'SalesOrderController@getCredential');

        #PAYMENT
        $app->get('/sales_orders/{soId}/invoices/{invId}/payments', 'PaymentController@getByInvoiceId');
        $app->get('/sales_orders/{soId}/invoices/{invId}/payments/create', 'PaymentController@create');
        $app->get('/sales_orders/{soId}/invoices/{invId}/payments/{id}', 'PaymentController@getByIdAndInvoiceId');
        $app->get('/sales_orders/{soId}/invoices/{invId}/payments/{id}/edit', 'PaymentController@edit');
        $app->post('/sales_orders/{soId}/invoices/{invId}/payments', 'PaymentController@store');
        $app->post('/sales_orders/{soId}/invoices/{invId}/payments/{id}/update', 'PaymentController@update');
        $app->delete('/sales_orders/{soId}/invoices/{invId}/payments', 'PaymentController@destroy');

        #SO
        rest('/sales_orders', 'SalesOrderController', $app);
        $app->get('/sales_orders/{soId}/details', 'SalesOrderController@getDetails');
        $app->post('/sales_orders/{soId}/details/{detailId}', 'SalesOrderController@updateDetail');

        #INVOICE
        $app->get('/sales_orders/{soId}/invoices', 'InvoiceController@getInvoiceBySoId');
        $app->get('/sales_orders/{soId}/invoices/{invId}', 'InvoiceController@getInvoiceByIdAndSoId');
        $app->get('/sales_orders/{soId}/invoices/{invId}/pdf', 'InvoiceController@generatePDFInvoiceByIdAndSoId');
        $app->get('/sales_orders/invoices/bulk-pdf', 'InvoiceController@generateBulkPDF');
        $app->get('/sales_orders/{soId}/invoices/{invId}/email', 'InvoiceController@sendInvoiceEmailByIdAndSoIdInDetail');

        $app->post('/sales_orders/{soId}/invoices/{invId}/mark_as_sent', 'InvoiceController@markAsSent');
        $app->post('/sales_orders/{soId}/invoices/{invId}/mark_as_void', 'InvoiceController@markAsVoid');

        $app->post('/sales_orders/{soId}/invoices/{invId}/email', 'InvoiceController@sendInvoiceEmailByIdAndSoId');

        #SHIPMENT
        $app->get('/sales_orders/shipments/bulk-label', 'SalesOrderController@generateShipmentLabelBulkPDF');
        $app->get('/sales_orders/{soId}/shipments', 'ShipmentController@fetch');
        $app->get('/sales_orders/{soId}/shipments/create', 'ShipmentController@create');
        $app->post('/sales_orders/{soId}/shipments', 'ShipmentController@store');
        $app->get('/sales_orders/{soId}/shipments/{id}/edit', 'ShipmentController@edit');
        $app->post('/sales_orders/{soId}/shipments/{id}/update', 'ShipmentController@update');
        $app->delete('/sales_orders/{soId}/shipments', 'ShipmentController@destroy');

        /**
         * Stock adjustment
         * Note: inventory app only access
         */
        $app->group(['prefix' => 'stock_adjustments',
            'middleware' => 'inventoryAccess'], function ($app) {
            $app->post('/setup', 'StockAdjustmentController@setup');
            $app->get('/create', 'StockAdjustmentController@create');
            $app->post('/', 'StockAdjustmentController@store');
            $app->get('/', 'StockAdjustmentController@fetch');
            $app->post('/{id}', 'StockAdjustmentController@update');
            $app->get('/{id}', 'StockAdjustmentController@detail');
            $app->delete('/{id}', 'StockAdjustmentController@delete');

            $app->group(['prefix' => 'history'], function ($app) {
                $app->get('/item', 'StockAdjustmentController@itemHistory');
                $app->get('/reason', 'StockAdjustmentController@reasonHistory');
            });
        });

        /**
         * Stock
         * Note: inventory app only access
         */
        $app->group(['prefix' => 'stocks',
            'middleware' => 'inventoryAccess'], function ($app) {
            $app->get('/detail', 'StockController@detail');
            $app->get('/', 'StockController@fetch');
            $app->post('/free_adjust', 'StockController@freeAdjust');
        });

        /**
         * Integration
         * Note: inventory app only access
         */
        $app->group(['prefix' => 'integration',
            'middleware' => 'inventoryAccess'], function ($app) {
            $app->group(['prefix' => 'lazada'], function ($app) {
                $app->group(['prefix' => 'api-config'], function ($app) {
                    $app->post('/', 'LazadaAPIConfigController@store');
                    $app->get('/', 'LazadaAPIConfigController@detail');
                    $app->delete('/', 'LazadaAPIConfigController@delete');
                });
                $app->group(['prefix' => 'item/{item_id}'], function ($app) {
                    $app->get('/aliases',
                        'LazadaItemAliasController@fetch');
                    $app->post('/aliases',
                        'LazadaItemAliasController@store');
                    $app->get('/aliases/{id}',
                        'LazadaItemAliasController@detail');
                    $app->post('/aliases/{id}',
                        'LazadaItemAliasController@update');
                    $app->delete('/aliases/{id}',
                        'LazadaItemAliasController@delete');
                });
            });
        });

        /**
         * My Sales Channel
         */
        $app->group(['prefix' => 'my_channels'], function ($app) {
            $app->get('/create', 'MySalesChannelController@create');
            $app->get('/{id}', 'MySalesChannelController@detail');
            $app->post('/{id}', 'MySalesChannelController@update');
            $app->delete('/{id}', 'MySalesChannelController@delete');
            $app->get('/', 'MySalesChannelController@fetch');
            $app->post('/', 'MySalesChannelController@store');
        });

        /**
         * Reason
         */
        $app->group(['prefix' => 'reasons'], function ($app) {
            $app->get('/create', 'ReasonController@create');
            $app->post('/', 'ReasonController@store');
            $app->get('/', 'ReasonController@fetch');
            $app->get('/{id}', 'ReasonController@detail');
            $app->post('/{id}', 'ReasonController@update');
            $app->delete('/{id}', 'ReasonController@delete');
        });

    });


$app->group(['prefix' => 'tests'], function ($app) {
    $app->get('guzzle_test', function (\Illuminate\Http\Request $request) {
        $cli = new \GuzzleHttp\Client();

        $param = [
            'headers' => [
                'Authorization' => "Bearer " . $request->bearerToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ];

        $response = $cli->get(env('GATEWAY_ASSET_API') . '/countries', $param);

        $data = $response->getBody()->getContents();
        return $data;

    });
//TODO(jee): for test only
    $app->get('/pi', function () use ($app) {
        phpinfo();
    });

    $app->get('/export_item', function () use ($app) {
        \App\Models\Item::inst()->exportMass();
    });

    $app->get('/import_item', function () use ($app) {
        \App\Models\Item::inst()->importMass("");
    });

    $app->get('/mailTest', function () use ($app) {
        MailUtil::send('jee.archer@gmail.com', 'jehan', 'test', 'testmail', 'the messages');
    });

    $app->get('/invoices/pdf',
        function (\Illuminate\Http\Request $request) use ($app) {

            $inv = \App\Models\Invoice::inst()
                ->nested()
                ->first();

            $filePath = 'temp/' . strtoupper($inv->invoice_number) . '.pdf';
            $disk = Storage::disk('s3');

            if ($request->get('isUrlType') == 'true' && $disk->has($filePath)) {
                return $disk->url($filePath);
            }

            $pdf = App::make('snappy.pdf.wrapper');
            $pdf->loadView('pdf_test', ['invoice' => $inv])
                ->setPaper('a4')
                ->setOrientation('portrait')
                ->setOption('margin-bottom', 0);

            if ($request->get('isTypeUrl') == 'true') {
                $disk->put($filePath, $pdf->output());
                return $disk->url($filePath);
            }

            header('Content-Type: application/pdf');
            return $pdf->inline();
        });

    $app->get('/shipment-label/html', function () use ($app) {
        return view('shipment.shipment_label_bak');
    });

    $app->get('/test', function () use ($app) {
        $url = 'https://s3-ap-southeast-1.amazonaws.com/sahitotest/985771e1f54daa7366e3603adb3f97bc86c39877/items/018c95dce141641473924840/7c54cdf4e5b52bcefc1fa7564642b35e9f238a24.jpg';
        $urlPath = parse_url($url, PHP_URL_PATH);
        return pathinfo($urlPath, PATHINFO_EXTENSION);

    });

    $app->get('test-illu-mail', function () {
//    Mail::to('jee.archer@gmail.com')->send(new \App\Mails\ReportImportMassMail());

        Mail::to('jee.archer@gmail.com')
            ->send(new ReportImportMassTestMail());

    });

    $app->get('/invoice/html', function () use ($app) {
        return view('invoice.invoice_bulk_pdf_template');
    });
});

#OPEN URL, IT'S FOR PUBLIC ACCESS
$app->group([
    'prefix' => 'api/v1/open/',
    'middleware' => ['authToken', 'localization']
], function ($app) {
    $app->group(['prefix' => 'my-sales-channels'], function ($app) {
        $app->get('/{id}', 'MySalesChannelController@detail');
        $app->get('/', 'MySalesChannelController@fetch');
    });

    $app->group(['prefix' => 'shop'], function ($app) {
        $app->get('/', 'Open\ShopController@fetch');
    });
});