<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});

/**
 * Factory definition for model App\Role.
 */
$factory->define(App\Role::class, function ($faker) {
    return [
        // Fields here
    ];
});

/**
 * Factory definition for model App\CommityLog.
 */
$factory->define(App\CommityLog::class, function ($faker) {
    return [
        'studant' => $faker->fillable,
    ];
});

/**
 * Factory definition for model App\Paymnet.
 */
$factory->define(App\Paymnet::class, function ($faker) {
    return [
        'amount' => $faker->fillable,
    ];
});

/**
 * Factory definition for model App\StudantInstallment.
 */
$factory->define(App\StudantInstallment::class, function ($faker) {
    return [
        'StudentId' => $faker->fillable,
        'InvoiceNumber' => $faker->fillable,
        'PaymentId' => $faker->fillable,
        'checkedBy' => $faker->fillable,
    ];
});

/**
 * Factory definition for model App\StudantAccount.
 */
$factory->define(App\StudantAccount::class, function ($faker) {
    return [
        'studantId' => $faker->fillable,
        'amount' => $faker->fillable,
        'scolarshop' => $faker->fillable,
        'tolls' => $faker->fillable,
    ];
});

/**
 * Factory definition for model App\StudantTolls.
 */
$factory->define(App\StudantTolls::class, function ($faker) {
    return [
        'year' => $faker->fillable,
        'amount' => $faker->fillable,
        'registration' => $faker->fillable,
    ];
});

/**
 * Factory definition for model App\Settings.
 */
$factory->define(App\Settings::class, function ($faker) {
    return [
        // Fields here
    ];
});

/**
 * Factory definition for model App\Cards.
 */
$factory->define(App\Cards::class, function ($faker) {
    return [
        'semesterId' => $faker->fillable,
        'studantId' => $faker->fillable,
        'userId' => $faker->fillable,
    ];
});

/**
 * Factory definition for model App\Transactions.
 */
$factory->define(App\Transactions::class, function ($faker) {
    return [
        'amount' => $faker->fillable,
        'leftover' => $faker->fillable,
        'payments' => $faker->fillable,
        'userId' => $faker->fillable,
        'studantId' => $faker->fillable,
    ];
});

/**
 * Factory definition for model App\AutoCash.
 */
$factory->define(App\AutoCash::class, function ($faker) {
    return [
        'transId' => $faker->fillable,
    ];
});

/**
 * Factory definition for model App\AutoBank.
 */
$factory->define(App\AutoBank::class, function ($faker) {
    return [
        'transId' => $faker->fillable,
    ];
});

/**
 * Factory definition for model App\SystemLog.
 */
$factory->define(App\SystemLog::class, function ($faker) {
    return [
        // Fields here
    ];
});
