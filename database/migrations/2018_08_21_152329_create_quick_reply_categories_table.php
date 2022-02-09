<?php

use Database\Utils\CustomBlueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuickReplyCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CustomBlueprint::inst()->create('quick_reply_categories', function (CustomBlueprint $table){
            $table->bigIncrements('category_id');
            $table->string('name', 100)->nullable(false);
            $table->boolean('category_status')->default(true);
            $table->bigInteger('organization_id')->unsigned()->nullable(false);

            $table->defaultColumn();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('quick_reply_categories');
    }
}
