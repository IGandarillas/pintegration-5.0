<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDireccionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('direccions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_address_prestashop');
            $table->string('address1');
            $table->string('country');
            $table->string('postcode');
            $table->string('city');
            $table->timestamps();

            $table->integer('id_state')->unsigned()->nullable();
            $table->foreign('id_state')
                ->references('id')->on('states')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->integer('client_id')->unsigned()->nullable()->unique();
            $table->foreign('client_id')
                ->references('id')->on('clients')
                ->onDelete('cascade')->onUpdate('cascade');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('direccions');
    }
}
