<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateReasonColumnInTableReason extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::Transaction(function () {
            DB::update("UPDATE reasons
                        SET reason='Barang dicuri'
                        WHERE reason = 'Stolen goods'");
            DB::update("UPDATE reasons
                        SET reason='Barang rusak'
                        WHERE reason = 'Damaged goods'");
            DB::update("UPDATE reasons
                        SET reason='Stock terbakar'
                        WHERE reason = 'Stock on fire'");
            DB::update("UPDATE reasons
                        SET reason='Stock dihapus'
                        WHERE reason = 'Stock written off'");
            DB::update("UPDATE reasons
                        SET reason='Revaluasi inventaris'
                        WHERE reason = 'Inventory revaluation'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
