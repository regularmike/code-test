<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {    
    $productModifiers = [
        'Deluxe',
        'Max',
        'Ultimate Edition',
        'JS',
        'X',
        'Turbo',
        'Executive',
        'Gold',
        'Signature',
        'Prime'
    ];
    $index = array_rand($productModifiers);

    return [
        'name' => sprintf('%s %s', ucfirst($faker->word), $productModifiers[$index]),
        'description' => $faker->text,
        'price' => $faker->randomNumber(2)        
    ];
});
