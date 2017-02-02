<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pushers', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('location_id');         // defined by READER
            $table->integer('reader_id');           // defined by POG
            $table->integer('product_id');          // defined by POG

            $table->string('tray_tag');             // defined by POG
            $table->string('shelf_number');         // defined by POG
            $table->string('location_number');      // defined by POG
            $table->integer('total_tags');          // defined by POG

            $table->double('tags_blocked', 4, 2)->nullable();   // defined by INV

            $table->double('item_count', 4, 2)->nullable();     // defined by INV
            $table->string('status')->nullable();               // defined by INV

            $table->boolean('oos')->default(false); // defined by INV
            $table->dateTime('oos_at')->nullable(); // defined by INV

            $table->boolean('low_stock')->default(false);
            $table->dateTime('low_stock_at')->nullable();

            $table->boolean('low_stock_notified')
                ->default(false);
            $table->boolean('oos_notified')
                ->default(false);
            $table->boolean('timed_oos_notified')
                ->default(false);

            $table->boolean('active')
                ->default(true);

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
        Schema::drop('pushers');
    }
}
