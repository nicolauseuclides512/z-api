<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateLogoUrlSalesChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::Transaction(function () {
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/bbm.png'
                        WHERE id=14");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/bukalapak.png'
                        WHERE id=7");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/elevenia.png'
                        WHERE id=8");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/facebook.png'
                        WHERE id=3");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/hijabenka.png'
                        WHERE id=16");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/instagram.png'
                        WHERE id=5");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/instashop.png'
                        WHERE id=22");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/kaskus.png'
                        WHERE id=6");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/lazada.png'
                        WHERE id=20");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/line.png'
                        WHERE id=24");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/line@.png'
                        WHERE id=13");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/lyke.png'
                        WHERE id=17");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/muslimarket.png'
                        WHERE id=18");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/qoo10.png'
                        WHERE id=10");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/shopee.png'
                        WHERE id=9");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/tokopedia.png'
                        WHERE id=4");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/twitter.png'
                        WHERE id=2");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/whatsapp_business.png'
                        WHERE id=23");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/whatsapp.png'
                        WHERE id=12");
            DB::update("UPDATE sales_channels SET
                        logo='https://s3-ap-southeast-1.amazonaws.com/asset.zuragan.com.dev/assets/store/sales-channels/zalora.png'
                        WHERE id=19");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
