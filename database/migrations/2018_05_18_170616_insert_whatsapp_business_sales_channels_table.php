<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertWhatsappBusinessSalesChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('sales_channels')->insert([
                ['channel_name' => 'Whatsapp Business', 'created_at' => time()],
                ['channel_name' => 'LINE', 'created_at' => time()]
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('sales_channels')
            ->where('channel_name', 'Whatsapp Business')
            ->delete();

        DB::table('sales_channels')
            ->where('channel_name', 'LINE')
            ->delete();
    }
}
