<?php

namespace CarterZenk\Tests\JsonApi\Encoder;

use CarterZenk\JsonApi\Encoder\JsonApiEncoder;
use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
use CarterZenk\JsonApi\Serializer\JsonApiSerializer;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use Slim\Http\Response;

class EncoderTest extends BaseTestCase
{
    public function testInvalidObjectThrowsException()
    {
        $encoder = new JsonApiEncoder(new JsonApiSerializer());
        $response = new Response();
        $this->expectException(InvalidDomainObjectException::class);
        $encoder->encodeResource(new \stdClass(), new Contact(), $this->getMockRequest(), $response);
    }

    public function testInvalidRelationshipThrowsException()
    {
        $encoder = new JsonApiEncoder(new JsonApiSerializer());
        $response = new Response();
        $this->expectException(InvalidDomainObjectException::class);
        $encoder->encodeRelationship(new \stdClass(), $this->getMockRequest(), $response, 'test');
    }
}
