<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTypeInSalesChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('sales_channels', function (Blueprint $table) {
        $table->string('type')->default('marketplace');
      });

      DB::table('sales_channels')
            ->where('channel_name', 'like', 'Whats%')
            ->orWhere('channel_name', 'like', 'LINE%')
            ->update(['type' => 'social']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('sales_channels', function (Blueprint $table) {
        $table->dropColumn('type');
      });
    }
}
