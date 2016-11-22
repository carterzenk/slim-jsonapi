<?php

namespace CarterZenk\Tests\JsonApi\Encoder;

use CarterZenk\JsonApi\Encoder\EncoderInterface;
use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use Slim\Http\Request;
use Slim\Http\Response;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;

class EncoderTest extends BaseTestCase
{
    public function testInvalidObjectThrowsException()
    {
        $encoder = $this->app->getContainer()->get(EncoderInterface::class);
        $exceptionFactory = new DefaultExceptionFactory();
        $request = Request::createFromEnvironment($this->app->getContainer()->get('environment'));
        $request = new \WoohooLabs\Yin\JsonApi\Request\Request($request, $exceptionFactory);
        $response = new Response();
        $this->expectException(InvalidDomainObjectException::class);
        $encoder->encodeResource(new \stdClass(), $request, $response);
    }

    public function testInvalidRelationshipThrowsException()
    {
        $encoder = $this->app->getContainer()->get(EncoderInterface::class);
        $exceptionFactory = new DefaultExceptionFactory();
        $request = Request::createFromEnvironment($this->app->getContainer()->get('environment'));
        $request = new \WoohooLabs\Yin\JsonApi\Request\Request($request, $exceptionFactory);
        $response = new Response();
        $this->expectException(InvalidDomainObjectException::class);
        $encoder->encodeRelationship(new \stdClass(), $request, $response, 'test');
    }
}
