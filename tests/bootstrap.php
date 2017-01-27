<?php

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use Laracasts\TestDummy\Factory;

require __DIR__.'/../vendor/autoload.php';

// Setup model factory.
Factory::$factoriesPath = __DIR__.'/Factories';

// Set sqlite database settings.
$dbSettings = [
    'driver'    => 'sqlite',
    'database'  => ':memory:',
    'prefix'    => ''
];

// Set model connection resolver.
$eloquentContainer = new Container();
$connectionFactory = new ConnectionFactory($eloquentContainer);
$connection = $connectionFactory->make($dbSettings);
$resolver = new ConnectionResolver();
$resolver->addConnection('default', $connection);
$resolver->setDefaultConnection('default');
Model::setConnectionResolver($resolver);

// Setup capsule manager.
$capsule = new Manager();
$capsule->addConnection($dbSettings);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Run db migrations.
$fileSystem = new Filesystem();
$classFinder = new ClassFinder();

foreach ($fileSystem->files(__DIR__.'/Migrations') as $file) {
    $fileSystem->requireOnce($file);
    $migrationClass = $classFinder->findClass($file);
    (new $migrationClass())->down();
    (new $migrationClass())->up();
}
