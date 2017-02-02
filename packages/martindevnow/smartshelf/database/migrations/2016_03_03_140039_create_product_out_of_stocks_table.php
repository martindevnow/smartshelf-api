<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductOutOfStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_out_of_stocks', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('product_id');
            $table->integer('location_id');

            $table->dateTime('oos_at');
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
        Schema::drop('product_out_of_stocks');
    }
}
