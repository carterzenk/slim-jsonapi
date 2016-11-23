<?php

namespace CarterZenk\JsonApi\Handlers;

use CarterZenk\JsonApi\Exceptions\MethodNotAllowed;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

class NotAllowedHandler extends AbstractErrorHandler
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $availableMethods)
    {
        $exception = new MethodNotAllowed($availableMethods);
        $errorDocument = $exception->getErrorDocument();
        return $errorDocument->getResponse($this->serializer, $response, null, []);
    }
}
