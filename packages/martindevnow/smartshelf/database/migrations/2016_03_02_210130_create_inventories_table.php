<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('pusher_id');
            $table->integer('product_id');
            $table->integer('reader_id');
            $table->integer('location_id');

            // Received from the reader
            $table->double('tags_blocked', 4, 2);
            $table->boolean('paddle_exposed')->default(false);

            // Calculated by the system
            $table->double('item_count', 4, 2);
            $table->string('status');
            $table->boolean('oos')
                ->default(false);

            $table->double('prev_item_count', 4, 2)->default(0);
            $table->double('product_item_count', 4, 2)->default(0);
            $table->integer('pusher_ooses')->default(0);
            $table->integer('number_of_pushers')->default(0);
            $table->dateTime('prev_oos_at')->nullable();


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
        Schema::drop('inventories');
    }
}
