<?php

namespace CarterZenk\Tests\JsonApi\Migrations;

use CarterZenk\Tests\JsonApi\Model\Organization;
use Faker\Factory;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Manager::schema()->create('organizations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 50)->nullable();
            $table->integer('type_id', false, true)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Manager::schema()->hasTable('organizations')) {
            Manager::schema()->drop('organizations');
        }
    }
}
