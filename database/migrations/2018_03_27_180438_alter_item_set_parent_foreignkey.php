<?php

use Database\Utils\CustomBlueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterItemSetParentForeignkey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CustomBlueprint::inst()->table('items', function (CustomBlueprint $table) {
            $table->bigInteger("category_id")->unsigned()->nullable()->change();

            $table->bigInteger("parent_id")->unsigned()->nullable()->change();

//            $table->foreign('parent_id')->references('item_id')->on('items');
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
