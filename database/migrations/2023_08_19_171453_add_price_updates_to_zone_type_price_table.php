<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceUpdatesToZoneTypePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zone_type_price', function (Blueprint $table) {
            $table->integer('additional_distance_start')->after('price_per_distance');
            $table->double('price_per_additional_distance', 10, 2)->default(0)->after('additional_distance_start');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zone_type_price', function (Blueprint $table) {
            //
        });
    }
}
