<?php

namespace CarterZenk\Tests\JsonApi\Migrations;

use CarterZenk\Tests\JsonApi\Model\OrganizationUser;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrganizationUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Manager::schema()->create('organization_users', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('org_id', false, true)->nullable();
            $table->integer('user_id', false, true)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Manager::schema()->hasTable('organization_users')) {
            Manager::schema()->drop('organization_users');
        }
    }
}
