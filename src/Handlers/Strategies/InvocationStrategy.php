<?php

namespace CarterZenk\JsonApi\Handlers\Strategies;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\Request;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;

class InvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @var ExceptionFactoryInterface
     */
    private $exceptionFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * JsonApiStrategy constructor.
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ExceptionFactoryInterface $exceptionFactory,
        SerializerInterface $serializer
    ) {
        $this->exceptionFactory = $exceptionFactory;
        $this->serializer = $serializer;
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
        $jsonApi = new JsonApi($request, $response, $this->exceptionFactory, $this->serializer);

        return call_user_func($callable, $jsonApi, $routeArguments);
    }
}
