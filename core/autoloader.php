<?php

declare(strict_types=1);

use MXRVX\ORM\EntityPathConfig;
use MXRVX\ORM\Tools\Packages;
use MXRVX\Telegram\Bot\Auth\App;

if (!\defined('MODX_CORE_PATH')) {

    $dir = __DIR__;
    while (!\str_ends_with($dir, DIRECTORY_SEPARATOR)) {
        $dir = \dirname($dir);

        $file = \implode(DIRECTORY_SEPARATOR, [$dir, 'core', 'config', 'config.inc.php']);
        if (\file_exists($file)) {
            require $file;
            break;
        }
    }
    unset($dir);

    if (!\defined('MODX_CORE_PATH')) {
        exit('Could not load MODX core');
    }
}

$file = MODX_CORE_PATH . 'vendor/autoload.php';
if (\file_exists($file)) {
    require $file;
}

unset($file);

/** @psalm-suppress MissingFile */
require_once __DIR__ . '/deprecated.php';

/** @var \modX $modx */
if (!isset($modx)) {
    /** @psalm-suppress MissingFile */
    if (!\class_exists(\modX::class) && \file_exists(MODX_CORE_PATH . 'model/modx/modx.class.php')) {
        require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
    }
    $modx = \modX::getInstance();
    $modx->initialize();
}

App::injectDependencies($modx);
/** @var \DI\Container $container */
$container = $container ?? \MXRVX\Autoloader\App::container();

if ($entityPathConfig = $container->get(EntityPathConfig::class)) {
    $entityPathConfig->addPath(App::NAMESPACE, Packages::getVendorEntitiesDirectory(App::NAMESPACE));
}
if (!$container->has(App::class)) {
    $container->set(App::class, \DI\autowire());
}
