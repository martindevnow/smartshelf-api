<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')
                ->unsigned();

            $table->integer('reportable_id')
                ->unsigned();
            $table->string('reportable_type');

            $table->string('type');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('format');

            $table->dateTime('requested_at');
            $table->dateTime('generated_at')
                ->nullable();

            $table->boolean('nightly')
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
        Schema::drop('reports');
    }
}
