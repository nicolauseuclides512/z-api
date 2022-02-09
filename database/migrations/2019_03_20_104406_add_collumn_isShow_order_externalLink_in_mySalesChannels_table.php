<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCollumnIsShowOrderExternalLinkInMySalesChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('my_sales_channels', function (Blueprint $table) {
        $table->boolean('is_shown')->default(1);
        $table->integer('order')->default(1);
        $table->string('external_link')->nullable();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('my_sales_channels', function (Blueprint $table) {
        $table->dropColumn('is_shown');
        $table->dropColumn('order');
        $table->dropColumn('external_link');
      });
    }
}
