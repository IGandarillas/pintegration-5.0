<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('prestashop_api')->nullable();
            $table->string('prestashop_url')->nullable();
            $table->string('pipedrive_api')->nullable();
            $table->string('address_field')->nullable();
            $table->string('email')->unique();
            $table->string('email_log')->unique();
            $table->string('password', 60);
            $table->dateTime('last_products_sync')->nullable()->default(\Carbon\Carbon::now());
            $table->dateTime('last_clients_sync')->nullable()->default(\Carbon\Carbon::now());
            $table->dateTime('last_addresses_sync')->nullable()->default(\Carbon\Carbon::now());
            $table->boolean('now_sync')->nullable()->default(false);
            $table->string('pipedrive_owner_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
