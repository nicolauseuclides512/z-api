<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisplayModeColumnInMySalesChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('my_sales_channels',
            function (Blueprint $table) {
                $table->smallInteger('display_mode')->default(1);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('my_sales_channels',
            function (Blueprint $table) {
                $table->dropColumn('display_mode');
            });
    }
}
