<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Subscription;
use Faker\Generator as Faker;

$factory->define(Subscription::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'start' => $faker->dateTime(),
        'end' => $faker->dateTime()
    ];
});
