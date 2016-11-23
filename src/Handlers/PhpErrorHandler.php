<?php

namespace CarterZenk\JsonApi\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WoohooLabs\Yin\JsonApi\Exception\ApplicationError;

class PhpErrorHandler extends AbstractErrorHandler
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param \Error $error
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \Error $error
    ) {
        $additionalMeta = $this->displayErrorDetails === true ? $this->getErrorMeta($error) : [];
        $error = new ApplicationError();
        $errorDocument = $error->getErrorDocument();

        return $errorDocument->getResponse($this->serializer, $response, null, $additionalMeta);
    }

    /**
     * @param \Error $error
     * @return array
     */
    protected function getErrorMeta(\Error $error)
    {
        return [
            "code" => $error->getCode(),
            "message" => $error->getMessage(),
            "file" => $error->getFile(),
            "line" => $error->getLine(),
            "trace" => $error->getTrace()
        ];
    }
}
