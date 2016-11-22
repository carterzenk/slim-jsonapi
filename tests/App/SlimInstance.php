<?php

namespace CarterZenk\Tests\JsonApi\App;

use CarterZenk\JsonApi\App\App;
use CarterZenk\JsonApi\Document\DocumentFactory;
use CarterZenk\JsonApi\Document\DocumentFactoryInterface;
use CarterZenk\JsonApi\Encoder\EncoderInterface;
use CarterZenk\JsonApi\Encoder\JsonApiEncoder;
use CarterZenk\JsonApi\Handlers\ErrorHandler;
use CarterZenk\JsonApi\Hydrator\Hydrator;
use CarterZenk\JsonApi\Serializer\JsonApiSerializer;
use CarterZenk\JsonApi\Serializer\SerializerInterface;
use CarterZenk\JsonApi\Transformer\Transformer;
use CarterZenk\Tests\JsonApi\Controller\ContactsController;
use CarterZenk\Tests\JsonApi\Controller\UsersController;
use CarterZenk\Tests\JsonApi\Handlers\InvocationStrategy;
use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use Interop\Container\ContainerInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use CarterZenk\JsonApi\Hydrator\HydratorInterface;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

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

        $container[InvocationStrategyInterface::class] = function (ContainerInterface $container) {
            return new InvocationStrategy(
                $container->get(ExceptionFactoryInterface::class)
            );
        };

        $container[DocumentFactoryInterface::class] = function (ContainerInterface $container) {
            return new DocumentFactory();
        };

        $container[ExceptionFactoryInterface::class] = function (ContainerInterface $container) {
            return new DefaultExceptionFactory();
        };

        $container[HydratorInterface::class] = function (ContainerInterface $container) {
            return new Hydrator();
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
                $container->get(HydratorInterface::class)
            );
        };

        $container['\CarterZenk\Tests\JsonApi\Controller\UsersController'] = function (
            ContainerInterface $container
        ) {
            return new UsersController(
                $container->get(EncoderInterface::class),
                $container->get(ExceptionFactoryInterface::class),
                $container->get(HydratorInterface::class)
            );
        };
    }

    private static function setRoutes(App $app)
    {
        $app->get(
            '/leads',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:indexResourceAction'
        );

        $app->get(
            '/leads/{id}',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:findResourceAction'
        );

        $app->get(
            '/leads/{id}/relationships/{relationship}',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:findRelationshipAction'
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
            '/leads/{id}',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:deleteResourceAction'
        );

        $app->post(
            '/leads',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:createResourceAction'
        );

        $app->patch(
            '/leads/{id}/relationships/{relationship}',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:updateRelationshipAction'
        );

        $app->patch(
            '/leads/{id}',
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController:updateResourceAction'
        );

        $app->patch(
            '/users/{id}',
            '\CarterZenk\Tests\JsonApi\Controller\UsersController:updateResourceAction'
        );
    }
}
