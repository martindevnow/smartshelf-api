<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductLowStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_low_stocks', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('product_id');
            $table->integer('location_id');

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
        Schema::drop('product_low_stocks');
    }
}
