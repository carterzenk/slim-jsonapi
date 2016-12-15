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
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });

        $faker = Factory::create();

        for ($i = 0; $i < 50; $i++) {
            $contact = new Contact();
            $contact->setRawAttributes([
                'f_name' => $faker->firstName,
                'l_name' => $faker->lastName,
                'email' => $faker->email,
                'title' => $faker->title,
                'phone' => $faker->phoneNumber,
                'phone_cell' => $faker->phoneNumber,
                'phone_office' => $faker->phoneNumber,
                'address' => $faker->address,
                'city' => $faker->city,
                'state' => $faker->stateAbbr,
                'zip' => $faker->postcode,
                'birthday' => $faker->dateTimeBetween('-30 years', '-10 years'),
                'created_at' => $faker->dateTimeBetween('-3 years', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 years', 'now')
            ]);

            $contact->setAttribute('owner_id', $faker->numberBetween(1, 5));
            $contact->setAttribute('assigned_id', $faker->numberBetween(1, 5));

            $contact->save();
        }
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
