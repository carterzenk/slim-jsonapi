<?php

namespace CarterZenk\JsonApi\Handlers;

use CarterZenk\JsonApi\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WoohooLabs\Yin\JsonApi\Exception\ApplicationError;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;
use WoohooLabs\Yin\JsonApi\Serializer\DefaultSerializer;


class ErrorHandler
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var bool
     */
    protected $displayErrorDetails;

    /**
     * JsonApiExceptionHandler constructor.
     * @param bool $displayErrorDetails
     */
    public function __construct($displayErrorDetails = false)
    {
        $this->serializer = new DefaultSerializer();
        $this->displayErrorDetails = (bool) $displayErrorDetails;
    }

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
            "code" => $exception->getCode(),
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine(),
            "trace" => $exception->getTrace()
        ];
    }
}
