<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_subscriptions', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('contact_id')
                ->index();
            $table->integer('location_id')
                ->index();
            $table->string('report_type')
                ->index();
            $table->string('report_format')
                ->index();

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
        Schema::drop('report_subscriptions');
    }
}
