<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @author Sam <samsulma828@gmail.com>.
 */
class AssetUomsTableSeeder extends Seeder
{

    public function run()
    {

      $organizations = DB::table('asset_uoms')->get();

      $now = time();

      foreach ($organizations as $key) {

        $organization = DB::table('asset_uoms')
                ->where('organization_id', $key->organization_id)
                ->get();

        // perulangan untuk mengecek berapa jumlah uoms
        $i = 0;
        foreach ($organization as $value) {
          $i++;
        }

        // untuk user lama yang masih memiliki 2 uoms
        if ($i == 2) {
          DB::table('asset_uoms')
          ->insert([
            [
              'name' => 'meter',
              'organization_id' => $key->organization_id,
              'description' => str_random(20),
              'uom_status' => 1,
              'created_at' => $now,
              'created_by' => $key->created_by,
              'updated_at' => $now
            ],
            [
              'name' => 'centimeter',
              'organization_id' => $key->organization_id,
              'description' => str_random(20),
              'uom_status' => 1,
              'created_at' => $now,
              'created_by' => $key->created_by,
              'updated_at' => $now
            ],
            [
              'name' => 'kilogram',
              'organization_id' => $key->organization_id,
              'description' => str_random(20),
              'uom_status' => 1,
              'created_at' => $now,
              'created_by' => $key->created_by,
              'updated_at' => $now
            ],
            [
              'name' => 'gram',
              'organization_id' => $key->organization_id,
              'description' => str_random(20),
              'uom_status' => 1,
              'created_at' => $now,
              'created_by' => $key->created_by,
              'updated_at' => $now
            ],
            [
              'name' => 'set',
              'organization_id' => $key->organization_id,
              'description' => str_random(20),
              'uom_status' => 1,
              'created_at' => $now,
              'created_by' => $key->created_by,
              'updated_at' => $now
            ],
          ]);
        }

      }

    }
}
