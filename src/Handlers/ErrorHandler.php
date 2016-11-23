<?php

namespace CarterZenk\JsonApi\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WoohooLabs\Yin\JsonApi\Exception\ApplicationError;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;

class ErrorHandler extends AbstractErrorHandler
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param \Exception $exception
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \Exception $exception
    ) {
        $additionalMeta = $this->displayErrorDetails === true ? $this->getExceptionMeta($exception) : [];

        if (!($exception instanceof JsonApiExceptionInterface)) {
            $exception = new ApplicationError();
        }

        $errorDocument = $exception->getErrorDocument();
        return $errorDocument->getResponse($this->serializer, $response, null, $additionalMeta);
    }

    /**
     * @param \Exception $exception
     * @return array
     */
    protected function getExceptionMeta(\Exception $exception)
    {
        return [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace()
        ];
    }
}
