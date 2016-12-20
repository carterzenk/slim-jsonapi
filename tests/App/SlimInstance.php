<?php

namespace CarterZenk\Tests\JsonApi\App;

use CarterZenk\JsonApi\App\App;
use CarterZenk\JsonApi\Document\DocumentFactory;
use CarterZenk\JsonApi\Document\DocumentFactoryInterface;
use CarterZenk\JsonApi\Encoder\EncoderInterface;
use CarterZenk\JsonApi\Encoder\JsonApiEncoder;
use CarterZenk\JsonApi\Exceptions\ExceptionFactory;
use CarterZenk\JsonApi\Handlers\ErrorHandler;
use CarterZenk\JsonApi\Hydrator\Hydrator;
use CarterZenk\JsonApi\Hydrator\ModelHydrator;
use CarterZenk\JsonApi\Hydrator\ResourceHydrator;
use CarterZenk\JsonApi\Serializer\JsonApiSerializer;
use CarterZenk\JsonApi\Serializer\SerializerInterface;
use CarterZenk\JsonApi\Strategy\Filtering\ColumnEqualsValue;
use CarterZenk\JsonApi\Strategy\Filtering\FilteringStrategyInterface;
use CarterZenk\Tests\JsonApi\Controller\ContactsController;
use CarterZenk\Tests\JsonApi\Controller\EloquentModelController;
use CarterZenk\Tests\JsonApi\Controller\UsersController;
use CarterZenk\Tests\JsonApi\Handlers\InvocationStrategy;
use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use Interop\Container\ContainerInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use CarterZenk\JsonApi\Exceptions\ExceptionFactoryInterface;
use CarterZenk\JsonApi\Hydrator\HydratorInterface;

class SlimInstance
{
    public static function getInstance()
    {
        $app = new App(['settings' => self::getSettings()]);

        $container = $app->getContainer();
        self::setDependencies($container);

        self::setRoutes($app);
        self::migrateData();

        return $app;
    }

    private static function getSettings()
    {
        return [
            'db' => [
                'driver'    => 'sqlite',
                'database'  => ':memory:',
                'prefix'    => ''
            ],
            'displayErrorDetails' => true,
            'outputBuffering' => false,
            'jsonApi' => [
                'encoderOptions' => JSON_PRETTY_PRINT,
                'baseUrl' => 'http://localhost'
            ]
        ];
    }

    private static function migrateData()
    {
        $fileSystem = new Filesystem();
        $classFinder = new ClassFinder();

        foreach ($fileSystem->files(__DIR__.'/../Migrations') as $file) {
            $fileSystem->requireOnce($file);
            $migrationClass = $classFinder->findClass($file);
            (new $migrationClass())->down();
            (new $migrationClass())->up();
        }
    }

    private static function setDependencies(ContainerInterface $container)
    {
        $container['foundHandler'] = function (ContainerInterface $container) {
            return $container->get(InvocationStrategyInterface::class);
        };

        $container['errorHandler'] = function (ContainerInterface $container) {
            return $container->get(ErrorHandler::class);
        };

        $container[ErrorHandler::class] = function (ContainerInterface $container) {
            return new ErrorHandler(true);
        };

        $container[FilteringStrategyInterface::class] = function (ContainerInterface $container) {
            return new ColumnEqualsValue();
        };

        $container[InvocationStrategyInterface::class] = function (ContainerInterface $container) {
            return new InvocationStrategy(
                $container->get(ExceptionFactoryInterface::class)
            );
        };

        $container[DocumentFactoryInterface::class] = function (ContainerInterface $container) {
            return new DocumentFactory();
        };

        $container[ExceptionFactoryInterface::class] = function (ContainerInterface $container) {
            return new ExceptionFactory();
        };

        $container[SerializerInterface::class] = function (ContainerInterface $container) {
            return new JsonApiSerializer(
                $container->get('settings')['jsonApi']['encoderOptions']
            );
        };

        $container[EncoderInterface::class] = function (ContainerInterface $container) {
            return new JsonApiEncoder(
                $container->get(SerializerInterface::class),
                $container->get(DocumentFactoryInterface::class),
                $container->get(ExceptionFactoryInterface::class)
            );
        };

        $container['\CarterZenk\Tests\JsonApi\Controller\ContactsController'] = function (
            ContainerInterface $container
        ) {
            return new ContactsController(
                $container->get(EncoderInterface::class),
                $container->get(ExceptionFactoryInterface::class),
                $container->get(FilteringStrategyInterface::class)
            );
        };

        $container['\CarterZenk\Tests\JsonApi\Controller\UsersController'] = function (
            ContainerInterface $container
        ) {
            return new UsersController(
                $container->get(EncoderInterface::class),
                $container->get(ExceptionFactoryInterface::class),
                $container->get(FilteringStrategyInterface::class)
            );
        };

        $container['\CarterZenk\Tests\JsonApi\Controller\EloquentModelController'] = function (
            ContainerInterface $container
        ) {
            return new EloquentModelController(
                $container->get(EncoderInterface::class),
                $container->get(ExceptionFactoryInterface::class),
                $container->get(FilteringStrategyInterface::class)
            );
        };
    }

    private static function setRoutes(App $app)
    {
        $app->get(
            '/contacts',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:indexResourceAction'
        );

        $app->get(
            '/contacts/{id}',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:findResourceAction'
        );

        $app->get(
            '/contacts/{id}/relationships/{relationship}',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:findRelationshipAction'
        );

        $app->get(
            '/users',
            '\CarterZenk\Tests\JsonApi\Controller\UsersController:indexResourceAction'
        );

        $app->get(
            '/users/{id}',
            '\CarterZenk\Tests\JsonApi\Controller\UsersController:findResourceAction'
        );

        $app->get(
            '/users/{id}/relationships/{relationship}',
            '\CarterZenk\Tests\JsonApi\Controller\UsersController:findRelationshipAction'
        );

        $app->delete(
            '/contacts/{id}',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:deleteResourceAction'
        );

        $app->post(
            '/contacts',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:createResourceAction'
        );

        $app->patch(
            '/contacts/{id}/relationships/{relationship}',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:updateRelationshipAction'
        );

        $app->patch(
            '/contacts/{id}',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:updateResourceAction'
        );

        $app->post(
            '/users',
            '\CarterZenk\Tests\JsonApi\Controller\UsersController:createResourceAction'
        );

        $app->patch(
            '/users/{id}',
            '\CarterZenk\Tests\JsonApi\Controller\UsersController:updateResourceAction'
        );
    }
}
