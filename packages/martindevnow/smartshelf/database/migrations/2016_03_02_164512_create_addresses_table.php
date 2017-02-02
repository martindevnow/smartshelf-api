<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('location_id');

            $table->string('name');
            $table->string('phone');
            $table->string('fax');

            $table->string('street_1');
            $table->string('street_2')->nullable();
            $table->string('city');
            $table->string('province');
            $table->string('postal_code');
            $table->string('country');

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
        Schema::drop('addresses');
    }
}
