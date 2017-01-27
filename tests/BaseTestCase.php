<?php

namespace CarterZenk\Tests\JsonApi;

use PHPUnit\Framework\TestCase;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

class BaseTestCase extends TestCase
{
    protected function getMockRequest()
    {
        return $this->prophesize(RequestInterface::class)->reveal();
    }
}
