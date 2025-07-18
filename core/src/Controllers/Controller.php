<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

abstract class Controller
{
    protected \modUser $modxUser;
    protected ?RequestInterface $request = null;
    protected ?ResponseInterface $response = null;
    protected ?RouteInterface $route = null;
    protected string|array $scope = '';
    protected array $properties = [];

    public function __construct(protected ContainerInterface $container, protected \modX $modx)
    {
        /** @var \modUser $user */
        $this->modxUser = $modx->getUser();
    }

    public function options(): ResponseInterface
    {
        $response = $this->success();
        if (\getenv('CORS')) {
            $response = $response
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'POST, GET, HEAD, OPTIONS, DELETE, PUT, PATCH, UPDATE');
        }

        return $response;
    }

    public function hasScope(string|array $scopes): bool
    {
        $scopes = \array_map(
            static fn($scope): string => (string) $scope,
            \is_array($scopes) ? $scopes : [$scopes],
        );

        foreach ($scopes as $scope) {
            if (\str_contains($scope, '/')) {
                if (!$this->modx->hasPermission($scope) && !$this->modx->hasPermission((string) \preg_replace('#/.*#', '', $scope))) {
                    return false;
                }
            } elseif (!$this->modx->hasPermission($scope)) {
                return false;
            }
        }

        return true;
    }

    public function checkScope(string $method): ?ResponseInterface
    {
        if ($method === 'options' || !$this->scope || (PHP_SAPI === 'cli' && !\getenv('PHPUNIT'))) {
            return null;
        }

        if (empty($this->modxUser->get('id')) || !(int) $this->modxUser->isAuthenticated((string) $this->modx->context->get('key'))) {
            return $this->failure('Authentication required', 401);
        }

        $scopes = \array_map(
            static fn(string $scope): string => $scope . '/' . $method,
            \is_array($this->scope) ? $this->scope : [$this->scope],
        );

        if ($this->hasScope($scopes)) {
            return null;
        }

        return $this->failure(\sprintf('You have no scope from required `%s` for this action', \implode(', ', $scopes)), 403);
    }

    public function failure(null|string|array $message = '', int $code = 422, string $reason = ''): ResponseInterface
    {
        return $this->response($message, $code, $reason);
    }

    public function success(null|string|array $data = [], int $code = 200, string $reason = ''): ResponseInterface
    {
        return $this->response($data, $code, $reason);
    }

    public function getProperty(string $key, mixed $default = null): mixed
    {
        return $this->properties[$key] ?? $default;
    }

    public function setProperty(string $key, mixed $value): void
    {
        $this->properties[$key] = $value;
    }

    public function unsetProperty(string $key): void
    {
        unset($this->properties[$key]);
    }

    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->initController($request, $response);

        $method = \strtolower($request->getMethod());
        if ($noScope = $this->checkScope($method)) {
            return $noScope;
        }

        if (!\method_exists($this, $method)) {
            return $this->failure('Method Not Allowed', 405);
        }

        try {
            /** @var ResponseInterface $response */
            $response = $this->{$method}();
        } catch (\Throwable $e) {
            $response = $this->handleException($e);
        }
        return $response;
    }

    protected function initController(RequestInterface $request, ResponseInterface $response): void
    {
        /** @var ServerRequestInterface $request */
        $routeContext = RouteContext::fromRequest($request);
        $this->route = $routeContext->getRoute();
        $this->request = $request;
        $this->response = $response;

        $method = \strtolower($request->getMethod());
        $properties = ($method === 'get') ? $request->getQueryParams() : $request->getParsedBody() ?? [];
        if (\is_array($properties)) {
            if ($method === 'delete') {
                $properties = \array_merge($properties, $request->getQueryParams());
            }
            $properties = \array_merge($properties, (array) $this->route?->getArguments());
            $this->setProperties($properties);
        }
    }

    protected function response(null|string|array $data, int $status = 200, string $reason = ''): ResponseInterface
    {
        $response = $this->response ?? new Response();
        if ($data !== null) {
            $response->getBody()->write(\json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
        if (\getenv('CORS')) {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', (string) $this->request?->getHeaderLine('HTTP_ORIGIN'));
        }

        return $response
            ->withStatus($status, $reason)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    protected function handleException(\Throwable $e): ResponseInterface
    {
        return $this->failure($e->getMessage(), 500);
    }
}
