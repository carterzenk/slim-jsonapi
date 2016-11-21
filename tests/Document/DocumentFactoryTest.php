<?php

namespace CarterZenk\Tests\JsonApi\Document;

use CarterZenk\JsonApi\Document\DocumentFactory;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Uri;

class DocumentFactoryTest extends BaseTestCase
{
    public function testBaseUriWithPort()
    {
        $request = Request::createFromEnvironment(new Environment());
        $request = $request->withUri(new Uri('http', 'localhost', 8080));
        $documentFactory = new DocumentFactory();

        $baseUri = $this->invokeMethod($documentFactory, 'getBaseUri', [$request]);
        $this->assertEquals('http://localhost:8080', $baseUri);
    }

    private function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
