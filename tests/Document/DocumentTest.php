<?php

namespace CarterZenk\Tests\JsonApi\Document;

use CarterZenk\JsonApi\Document\DocumentFactory;
use CarterZenk\JsonApi\Transformer\Transformer;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use Slim\Http\Request;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Schema\JsonApi;

class DocumentTest extends BaseTestCase
{
    public function testNoJsonApiVersion()
    {
        $transformer = new Transformer();
        $documentFactory = new DocumentFactory($transformer, null);

        $exceptionFactory = new DefaultExceptionFactory();
        $request = Request::createFromEnvironment($this->app->getContainer()->get('environment'));
        $request = new \WoohooLabs\Yin\JsonApi\Request\Request($request, $exceptionFactory);

        $document = $documentFactory->createResourceDocument($request);

        $meta = ['test' => 'test'];
        $resource = Contact::find(1);

        $document->getMetaContent($resource, $meta);

        $id = $document->getResourceId();
        $this->assertEquals(1, $id);

        $this->assertNull($document->getJsonApi());
    }

    public function testWithJsonApiVersion()
    {
        $transformer = new Transformer();
        $documentFactory = new DocumentFactory($transformer, '1.0');

        $exceptionFactory = new DefaultExceptionFactory();
        $request = Request::createFromEnvironment($this->app->getContainer()->get('environment'));
        $request = new \WoohooLabs\Yin\JsonApi\Request\Request($request, $exceptionFactory);

        $document = $documentFactory->createResourceDocument($request);

        $jsonApi = $document->getJsonApi();
        $this->assertInstanceOf(JsonApi::class, $jsonApi);

        $version = $jsonApi->getVersion();
        $this->assertEquals('1.0', $version);
    }
}
