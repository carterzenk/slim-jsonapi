<?php

namespace CarterZenk\JsonApi\App;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Eloquent\Model;
use Slim\App as SlimApp;

class App extends SlimApp
{
    public function __construct($container)
    {
        $this->bootEloquent($container);
        parent::__construct($container);
    }

    private function bootEloquent($container)
    {
        $connectionSettings = $container['settings']['db'];

        // Set model connection resolver.
        $eloquentContainer = new Container();
        $connectionFactory = new ConnectionFactory($eloquentContainer);
        $connection = $connectionFactory->make($connectionSettings);
        $resolver = new ConnectionResolver();
        $resolver->addConnection('default', $connection);
        $resolver->setDefaultConnection('default');
        Model::setConnectionResolver($resolver);

        // Setup capsule manager.
        $capsule = new Manager();
        $capsule->addConnection($connectionSettings);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
