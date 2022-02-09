<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSalesOrderAndInvoiceAddPhone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('billing_phone', 20)->nullable();
            $table->string('billing_mobile', 20)->nullable();

            $table->string('shipping_phone', 20)->nullable();
            $table->string('shipping_mobile', 20)->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('billing_phone', 20)->nullable();
            $table->string('billing_mobile', 20)->nullable();

            $table->string('shipping_phone', 20)->nullable();
            $table->string('shipping_mobile', 20)->nullable();
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
            $table->dropColumn('billing_phone');
            $table->dropColumn('billing_mobile');

            $table->dropColumn('shipping_phone');
            $table->dropColumn('shipping_mobile');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('billing_phone');
            $table->dropColumn('billing_mobile');

            $table->dropColumn('shipping_phone');
            $table->dropColumn('shipping_mobile');
        });
    }
}
