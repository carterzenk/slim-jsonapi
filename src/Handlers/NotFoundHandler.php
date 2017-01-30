<?php

namespace CarterZenk\JsonApi\Handlers;

use CarterZenk\JsonApi\Exceptions\ResourceNotFound;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotFoundHandler extends AbstractErrorHandler
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $exception = new ResourceNotFound();
        $errorDocument = $exception->getErrorDocument();
        return $errorDocument->getResponse($this->serializer, $response, null, []);
    }
}
