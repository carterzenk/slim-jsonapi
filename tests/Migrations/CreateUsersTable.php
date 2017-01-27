<?php

namespace CarterZenk\Tests\JsonApi\Migrations;

use CarterZenk\Tests\JsonApi\Model\User;
use Faker\Factory;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Manager::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('f_name', 50)->nullable();
            $table->string('l_name', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('password', 50)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('phone_cell', 25)->nullable();
            $table->string('phone_office', 25)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip', 15)->nullable();
            $table->integer('timezone')->nullable();
            $table->integer('active_id', false, true)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Manager::schema()->hasTable('users')) {
            Manager::schema()->drop('users');
        }
    }
}
