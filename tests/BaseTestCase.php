<?php

namespace CarterZenk\Tests\JsonApi;

use CarterZenk\Tests\JsonApi\App\SlimInstance;
use Guzzle\Http\Message\Header;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Stream;
use Slim\Http\Uri;
use There4\Slim\Test\WebTestCase;

class BaseTestCase extends WebTestCase
{
    public function getSlimInstance()
    {
        return SlimInstance::getInstance();
    }
}
