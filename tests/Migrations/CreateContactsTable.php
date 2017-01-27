<?php

namespace CarterZenk\Tests\JsonApi\Migrations;

use CarterZenk\Tests\JsonApi\Model\Contact;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Faker\Factory;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Manager::schema()->create('leads', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('f_name', 50)->nullable();
            $table->string('l_name', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('title', 50)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('phone_cell', 25)->nullable();
            $table->string('phone_office', 25)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip', 15)->nullable();
            $table->integer('owner_id', false, true)->nullable();
            $table->integer('assigned_id', false, true)->nullable();
            $table->date('birthday')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Manager::schema()->hasTable('leads')) {
            Manager::schema()->drop('leads');
        }
    }
}
