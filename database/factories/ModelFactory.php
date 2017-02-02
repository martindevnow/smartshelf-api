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
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});




/**
 * Engineering Namespace
 */
$factory->define(\Martindevnow\Smartshelf\Engineering\Inventory::class, function (Faker\Generator $faker) {
    return [
        'pusher_id'     => factory(\Martindevnow\Smartshelf\Engineering\Pusher::class)->create()->id,
        'reader_id'     => factory(\Martindevnow\Smartshelf\Engineering\Reader::class)->create()->id,
        'location_id'   => factory(\Martindevnow\Smartshelf\Retailer\Location::class)->create()->id,
        'product_id'    => factory(\Martindevnow\Smartshelf\Product\Product::class)->create()->id,
        'tags_blocked'  => $faker->numberBetween(0,10),
        'paddle_exposed'=> false,
        'item_count'    => $faker->numberBetween(0,10),
        'status'        => $faker->numberBetween(0,10),
        'oos'           => false,
    ];
});

$factory->define(\Martindevnow\Smartshelf\Engineering\Pusher::class, function (Faker\Generator $faker) {
    return [
        'location_id'   => factory(\Martindevnow\Smartshelf\Retailer\Location::class)->create()->id,
        'reader_id'     => factory(\Martindevnow\Smartshelf\Engineering\Reader::class)->create()->id,
        'product_id'    => factory(\Martindevnow\Smartshelf\Product\Product::class)->create()->id,
        'tray_tag'      => "FF00000". $faker->numberBetween(10000,99999),
        'shelf_number'  => "FS0" . $faker->numberBetween(1,9),
        'location_number'       => $faker->numberBetween(1,20),
        'total_tags'       => $faker->numberBetween(4,6),
//        'item_count'    => $faker->numberBetween(0,12),
//        'status'        => $faker->numberBetween(0,12),
        'oos'                   => false,
        'oos_at'                => null,
        'low_stock_notified'    => false,
        'oos_notified'          => false,
        'timed_oos_notified'    => false,
        'active'                => true,
    ];
});

$factory->define(\Martindevnow\Smartshelf\Engineering\PusherLowStock::class, function (Faker\Generator $faker) {
    return [
        'pusher_id'     => factory(\Martindevnow\Smartshelf\Engineering\Pusher::class)->create()->id,
        'product_id'    => factory(\Martindevnow\Smartshelf\Product\Product::class)->create()->id,
        'location_id'   => factory(\Martindevnow\Smartshelf\Retailer\Location::class)->create()->id,
        'low_stock_at'  => Carbon::now()->toDateTimeString(),
        'restocked_at'  => null
    ];
});

$factory->define(\Martindevnow\Smartshelf\Engineering\PusherOutOfStock::class, function (Faker\Generator $faker) {
    return [
        'pusher_id'     => factory(\Martindevnow\Smartshelf\Engineering\Pusher::class)->create()->id,
        'product_id'    => factory(\Martindevnow\Smartshelf\Product\Product::class)->create()->id,
        'location_id'   => factory(\Martindevnow\Smartshelf\Retailer\Location::class)->create()->id,
        'oos_at'        => Carbon::now()->toDateTimeString(),
        'restocked_at'  => null
    ];
});

$factory->define(\Martindevnow\Smartshelf\Engineering\Reader::class, function (Faker\Generator $faker) {
    return [
        'mac_address'   => $faker->macAddress,
        'ip_address'    => $faker->ipv4,
        'location_id'   => factory(\Martindevnow\Smartshelf\Retailer\Location::class)->create()->id,
    ];
});





/**
 * Product Namespace
 */
$factory->define(\Martindevnow\Smartshelf\Product\Brand::class, function (Faker\Generator $faker) {
    $name = $faker->name;
    return [
        'name' => $name,
        'code' => strtolower(str_ireplace(' ', '-', $name)),
    ];
});

$factory->define(\Martindevnow\Smartshelf\Product\Product::class, function (Faker\Generator $faker) {
    return [
        'parent_id'     => null,
        'brand_id'      => factory(\Martindevnow\Smartshelf\Product\Brand::class)->create()->id,

        'upc'           => $faker->email,
        'name'          => $faker->name,
        'flavor'        => $faker->name,

        'pack_size'     => $faker->randomElement(['KS', 'REG']),
        'pack_quantity' => $faker->randomElement(['20', '25']),
        'pack_depth_in' => rand(85,115) / 100,

        'blocksPaddleTag' => true,
        'hasImage'      => false,
    ];
});

$factory->define(\Martindevnow\Smartshelf\Product\ProductLowStock::class, function (Faker\Generator $faker) {
    return [
        'product_id'    => factory(\Martindevnow\Smartshelf\Product\Product::class)->create()->id,
        'location_id'   => factory(\Martindevnow\Smartshelf\Retailer\Location::class)->create()->id,
        'low_stock_at'  => Carbon::now()->toDateTimeString(),
        'restocked_at'  => Carbon::now()->toDateTimeString(),
    ];
});

$factory->define(\Martindevnow\Smartshelf\Product\ProductOutOfStock::class, function (Faker\Generator $faker) {
    return [
        'product_id'    => factory(\Martindevnow\Smartshelf\Product\Product::class)->create()->id,
        'location_id'   => factory(\Martindevnow\Smartshelf\Retailer\Location::class)->create()->id,
        'oos_at'        => Carbon::now()->toDateTimeString(),
        'restocked_at'  => Carbon::now()->toDateTimeString(),
    ];
});





/**
 * Retailer Namespace
 */
$factory->define(\Martindevnow\Smartshelf\Retailer\Address::class, function(Faker\Generator $faker) {
    return [
        'name'          => $faker->name ,
        'street_1'      => $faker->streetAddress,
        'street_2'      => '',
        'city'          => $faker->city,
        'province'      => $faker->countryCode,
        'postal_code'   => $faker->postcode,
        'country'       => $faker->country,
    ];
});

$factory->define(\Martindevnow\Smartshelf\Retailer\Banner::class, function (Faker\Generator $faker) {
    $name = $faker->name;
    return [
        'name' => $name,
        'code' => strtolower(str_ireplace(' ', '-', $name)),
        'retailer_id'   => factory(\Martindevnow\Smartshelf\Retailer\Retailer::class)->create()->id,
    ];
});

$factory->define(\Martindevnow\Smartshelf\Retailer\Location::class, function (Faker\Generator $faker) {
    $name = $faker->name;
    return [
        'name' => $name,
        'code' => strtolower(str_ireplace(' ', '-', $name)),
        'template'      => $faker->randomElement(['nss', 'itc']),
        'banner_id'     => factory(\Martindevnow\Smartshelf\Retailer\Banner::class)->create()->id,
    ];
});

$factory->define(\Martindevnow\Smartshelf\Retailer\Retailer::class, function (Faker\Generator $faker) {
    $name = $faker->name;
    return [
        'name' => $name,
        'code' => strtolower(str_ireplace(' ', '-', $name)),
    ];
});