#!/usr/bin/php
<?php

// Usage: ./address.php [OPTION]...
//
// Options:
//   -s    Search term
//   -u    Username
//   -f    flush database
//   -m    Max number of random items to add
//   -d    Database to use
//
// Example: address.php -u tomasz -s Miss -r

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'vendor/autoload.php';

$redis = new Redis();
$redis_host = getenv('REDIS_HOST') ?: 'redis';
$redis_port = getenv('REDIS_PORT') ?: 6379;
if (!$redis->connect($redis_host, $redis_port)) {
    throw new \Exception("Couldn't connect to redis");
}

$options = getopt("s:d:u:m:f");
$username = $options["u"] ?? "jane_admin";
$search = $options["s"] ?? '*';
$max = $options["m"] ?? 5;
$db = $options["d"] ?? 1;
$redis->select($db);

if (isset($options['f'])) {
    echo "flushing database $db\n";
    $redis->flushDb($db);
}

$faker = Faker\Factory::create();
$faker->addProvider(new Faker\Provider\en_US\PhoneNumber($faker));
$faker->addProvider(new Faker\Provider\Internet($faker));

$count = $redis->sSize($username) + $redis->zSize($username);

for ($i = $count; $i < $max; $i++) {

    $contact = [
        "name" => $faker->name,
        "email"  => $faker->email,
        "phone" => $faker->phoneNumber
    ];
    echo "Added: ".join(' : ', $contact)."\n";

    array_unshift($contact, $username);
    $key = join(':', $contact);

    $multi = $redis->multi();
    $multi->sAdd($username, $key);
    foreach ($contact as $hashKey => $hashValue) {
        $multi->hSet($key, $hashKey, $hashValue);
    }
    $multi->exec();
}
