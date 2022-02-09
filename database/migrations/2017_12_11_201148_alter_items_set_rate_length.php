<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterItemsSetRateLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            //
            $table->decimal('sales_rate', 12, 2)->nullable()->change();
            $table->decimal("compare_rate", 12, 2)->nullable()->change();
            $table->decimal('purchase_rate', 12, 2)->nullable()->change();
            $table->decimal('inventory_rate', 12, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            //
        });
    }
}
