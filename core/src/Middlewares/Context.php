<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Context implements MiddlewareInterface
{
    public function __construct(protected \modX $modx) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contextKey = (string) ($request->getQueryParams()['context'] ?? 'web');
        if ($contextKey !== (string) $this->modx->context->get('key')) {
            $this->modx->switchContext($contextKey);
        }

        return $handler->handle($request);
    }
}
