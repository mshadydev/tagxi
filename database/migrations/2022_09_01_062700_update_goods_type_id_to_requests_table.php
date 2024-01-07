<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGoodsTypeIdToRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {

            $table->unsignedInteger('goods_type_id')->after('user_id')->nullable();
            $table->string('goods_type_quantity')->after('goods_type_id')->nullable();

            $table->foreign('goods_type_id')
                    ->references('id')
                    ->on('goods_types')
                    ->onDelete('cascade');
                 });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            //
        });
    }
}
