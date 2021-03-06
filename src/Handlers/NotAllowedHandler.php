<?php

namespace CarterZenk\JsonApi\Handlers;

use CarterZenk\JsonApi\Exceptions\MethodNotAllowed;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotAllowedHandler extends AbstractErrorHandler
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $availableMethods)
    {
        $exception = new MethodNotAllowed($availableMethods);
        $errorDocument = $exception->getErrorDocument();
        return $errorDocument->getResponse($this->serializer, $response, null, []);
    }
}
