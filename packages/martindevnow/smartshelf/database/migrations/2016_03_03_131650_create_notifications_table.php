<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_class');
            $table->string('view_file');
            $table->boolean('forMobile');
            $table->timestamps();
        });


        Schema::create('contact_notification', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contact_id')
                ->unsigned();
            $table->integer('notification_id')
                ->unsigned();
            $table->integer('location_id')
                ->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('notifications');
        Schema::drop('contact_notification');
    }
}
