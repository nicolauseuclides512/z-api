<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRenameSalesOrdersAndInvoiceCarrier extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->renameColumn('carrier_id', 'shipping_carrier_id');
            $table->renameColumn('carrier_code', 'shipping_carrier_code');
            $table->renameColumn('carrier_name', 'shipping_carrier_name');
            $table->renameColumn('carrier_service', 'shipping_carrier_service');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('carrier_id', 'shipping_carrier_id');
            $table->renameColumn('carrier_code', 'shipping_carrier_code');
            $table->renameColumn('carrier_name', 'shipping_carrier_name');
            $table->renameColumn('carrier_service', 'shipping_carrier_service');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->renameColumn('shipping_carrier_id', 'carrier_id');
            $table->renameColumn('shipping_carrier_code', 'carrier_code');
            $table->renameColumn('shipping_carrier_name', 'carrier_name');
            $table->renameColumn('shipping_carrier_service', 'carrier_service');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('shipping_carrier_id', 'carrier_id');
            $table->renameColumn('shipping_carrier_code', 'carrier_code');
            $table->renameColumn('shipping_carrier_name', 'carrier_name');
            $table->renameColumn('shipping_carrier_service', 'carrier_service');
        });
    }
}
