<?php

use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\Organization;
use CarterZenk\Tests\JsonApi\Model\OrganizationUser;
use CarterZenk\Tests\JsonApi\Model\User;

if (isset($factory, $faker) && is_callable($factory)) {
    $factory(Contact::class, [
        'owner_id' => 'factory:'.User::class,
        'assigned_id' => 'factory:'.User::class,
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

    $factory(User::class, [
        'f_name' => $faker->firstName,
        'l_name' => $faker->lastName,
        'email' => $faker->email,
        'password' => $faker->password,
        'phone' => $faker->phoneNumber,
        'phone_cell' => $faker->phoneNumber,
        'phone_office' => $faker->phoneNumber,
        'address' => $faker->address,
        'city' => $faker->city,
        'state' => $faker->stateAbbr,
        'zip' => $faker->postcode,
        'timezone' => 5,
        'created_at' => $faker->dateTimeBetween('-3 years', 'now'),
        'updated_at' => $faker->dateTimeBetween('-3 years', 'now')
    ]);

    $factory(Organization::class, [
        'name' => $faker->company,
        'type_id' => $faker->numberBetween(0, 5),
        'created_at' => $faker->dateTimeBetween('-3 years', 'now'),
        'updated_at' => $faker->dateTimeBetween('-3 years', 'now')
    ]);

    $factory(OrganizationUser::class, [
        'org_id' => 'factory:'.Organization::class,
        'user_id' => 'factory:'.User::class
    ]);
}