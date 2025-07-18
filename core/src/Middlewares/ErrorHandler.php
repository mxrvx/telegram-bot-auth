<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Middlewares;

use Psr\Http\Message\ResponseInterface;

/**
 * @psalm-suppress MissingConstructor
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ErrorHandler extends \Slim\Handlers\ErrorHandler
{
    protected function respond(): ResponseInterface
    {
        $exception = $this->exception;
        $response = $this->responseFactory->createResponse();

        $payload = [
            'error' => true,
            'message' => $exception->getMessage(),
        ];

        /** @psalm-suppress PossiblyFalseArgument */
        $response->getBody()->write(\json_encode($payload));

        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus((int) ($exception->getCode() ?: 500));
    }
}
