<?php

namespace CarterZenk\Tests\JsonApi\Transformer;

use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
use CarterZenk\JsonApi\Model\Paginator;
use CarterZenk\JsonApi\Transformer\Container;
use CarterZenk\JsonApi\Transformer\LinksFactory;
use CarterZenk\JsonApi\Transformer\ResourceTransformer;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\User;
use Illuminate\Database\Eloquent\Collection;
use Slim\Http\Uri;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class TransformerContainerTest extends BaseTestCase
{
    public function testGetTransformerByModel()
    {
        $container = $this->getContainer();
        $transformer = $container->get(new Contact());
        $this->checkTransformer($transformer);
    }

    public function testGetTransformerByClassName()
    {
        $container = $this->getContainer();
        $transformer = $container->get(User::class);
        $this->checkTransformer($transformer);
    }

    public function testGetTransformerWithInvalidObjectThrowsException()
    {
        $container = $this->getContainer();
        $this->expectException(\InvalidArgumentException::class);
        $container->get([]);
    }

    private function getContainer()
    {
        $uri = new Uri('http', 'localhost', 8000);
        $linksFactory = new LinksFactory($uri);
        return new Container($linksFactory);
    }

    private function checkTransformer($transformer)
    {
        $this->assertInstanceOf(ResourceTransformer::class, $transformer);
    }
}
