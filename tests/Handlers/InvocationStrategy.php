<?php

namespace CarterZenk\Tests\JsonApi\Handlers;

use CarterZenk\JsonApi\Serializer\JsonApiSerializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\Request;
use WoohooLabs\Yin\JsonApi\Serializer\DefaultSerializer;

class InvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @var ExceptionFactoryInterface
     */
    private $exceptionFactory;

    /**
     * JsonApiStrategy constructor.
     * @param ExceptionFactoryInterface $exceptionFactory
     */
    public function __construct(ExceptionFactoryInterface $exceptionFactory)
    {
        $this->exceptionFactory = $exceptionFactory;
    }

    /**
     * @param callable $callable
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $routeArguments
     * @return mixed
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ) {
        $request = new Request($request, $this->exceptionFactory);
        $jsonApi = new JsonApi($request, $response, $this->exceptionFactory, new DefaultSerializer());

        return call_user_func($callable, $jsonApi, $routeArguments);
    }
}
