<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');

            $table->text('content');

            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_extension');

            // polymorphic relations
            $table->integer('attachmentable_id')->nullable();
            $table->string('attachmentable_type')->nullable();

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
        Schema::drop('attachments');
    }
}
