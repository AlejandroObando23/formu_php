<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->getDatabaseManager()->extend('mongodb', function($config, $name) {
    $config['name'] = $name;
    return new \Jenssegers\Mongodb\Connection($config);
});

$capsule->addConnection([
    'driver'   => 'mongodb',
    'dsn'      => getenv('MONGODB_URI'),
    'database' => 'mi_base_de_datos',
], 'mongodb');

$capsule->setAsGlobal();
$capsule->bootEloquent();
