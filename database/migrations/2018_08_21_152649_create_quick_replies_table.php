<?php

use Database\Utils\CustomBlueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuickRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CustomBlueprint::inst()->create('quick_replies', function (CustomBlueprint $table){
            $table->bigIncrements('quick_reply_id');
            $table->string('name', 100)->nullable(false);
            $table->string('description', 100)->nullable(false);
            $table->bigInteger('category_id')->unsigned()->nullable(false);
            $table->boolean('status')->default(true);
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
        Schema::drop('quick_replies');
    }
}
