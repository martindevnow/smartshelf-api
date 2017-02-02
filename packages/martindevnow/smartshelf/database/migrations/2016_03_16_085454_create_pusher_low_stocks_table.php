<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePusherLowStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pusher_low_stocks', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('pusher_id');
            $table->integer('location_id');
            $table->integer('product_id');

            $table->dateTime('low_stock_at');
            $table->dateTime('restocked_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pusher_low_stocks');
    }
}
