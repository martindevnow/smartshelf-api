<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('parent_id')->nullable();
            $table->integer('brand_id')->nullable();

            $table->string('upc')->index();
            $table->string('carton_upc')->index()->nullable();
            $table->string('code')->index()->nullable();

            $table->string('name')->nullable();
            $table->string('flavor')->nullable();

            $table->string('pack_size')->nullable();        // KS or Reg
            $table->integer('pack_quantity')->nullable();   // 20 or 25
            $table->double('pack_depth_in', 4, 2)->default(1);  // from 0.5 to 1.5 ish (more for cartons)

            $table->boolean('blocksPaddleTag')->default(true); // packs with FOIL = true
            $table->boolean('hasImage')->default(false);

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
        Schema::drop('products');
    }
}
