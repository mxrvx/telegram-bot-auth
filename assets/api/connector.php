<?php

declare(strict_types=1);

use DI\Bridge\Slim\Bridge;
use MXRVX\Telegram\Bot\Auth\App;
use MXRVX\Telegram\Bot\Auth\Middlewares\Context;
use MXRVX\Telegram\Bot\Auth\Middlewares\ErrorHandler;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;

if (isset($_SERVER['QUERY_STRING'])) {
    while (str_contains($_SERVER['QUERY_STRING'], '&amp;')) {
        $_SERVER['QUERY_STRING'] = html_entity_decode($_SERVER['QUERY_STRING']);
    }
}

$_SERVER['REQUEST_URI'] = str_replace('/assets/components', '', $_SERVER['REQUEST_URI']);

/** @var \modX $modx */
/** @var \DI\Container $container */
/** @psalm-suppress MissingFile */
require dirname(__DIR__, 2) . '/core/autoloader.php';

$modx = \modX::getInstance(\modX::class);
$modx->initialize();

$container = \MXRVX\Autoloader\App::container();
$container->set(ResponseFactoryInterface::class, \DI\autowire(ResponseFactory::class));

$app = Bridge::create($container);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = new ErrorHandler(
    $app->getCallableResolver(),
    $app->getResponseFactory()
);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->setBasePath('/'.App::NAMESPACE);
$app->add(Context::class);

/** @psalm-suppress MissingFile */
require __DIR__ . '/routes.php';

try {
    $app->run();
} catch (\Slim\Exception\HttpNotFoundException $e) {
    http_response_code(404);
    echo json_encode('Not Found');
} catch (\Throwable $e) {
    $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf("\nError: %s\nFile: %s \nLine: %s", $e->getMessage(), $e->getFile(), $e->getLine()));
    http_response_code(500);
    echo json_encode('Internal Server Error');
}
