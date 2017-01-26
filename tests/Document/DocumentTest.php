<?php

namespace CarterZenk\Tests\JsonApi\Document;

use CarterZenk\JsonApi\Document\DocumentFactory;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use Illuminate\Database\Eloquent\Model;
use Slim\Http\Environment;
use Slim\Http\Request;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Schema\JsonApi;

class DocumentTest extends BaseTestCase
{
    private function getDocument($jsonApiVersion = null, $modelClass)
    {
        $documentFactory = new DocumentFactory($jsonApiVersion);
        $exceptionFactory = new DefaultExceptionFactory();

        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/leads',
            'QUERY_STRING' => '',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '8080'
        ]));

        $request = new \WoohooLabs\Yin\JsonApi\Request\Request($request, $exceptionFactory);

        return $documentFactory->createResourceDocument($request, $modelClass);
    }

    public function testNoJsonApiVersion()
    {
        $document = $this->getDocument(null, Contact::class);

        $this->assertNull($document->getJsonApi());
    }

    public function testWithJsonApiVersion()
    {
        $document = $this->getDocument('1.0', Contact::class);

        $jsonApi = $document->getJsonApi();
        $this->assertInstanceOf(JsonApi::class, $jsonApi);

        $version = $jsonApi->getVersion();
        $this->assertEquals('1.0', $version);
    }

    public function testLinksWithPort()
    {
        $document = $this->getDocument(null, Contact::class);
        $links = $document->getLinks();

        $this->assertEquals('/leads', $links->getSelf()->getHref());
        $this->assertEquals('http://localhost:8080', $links->getBaseUri());
    }
}
