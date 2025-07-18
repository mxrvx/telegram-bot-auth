<?php

/** @var Slim\App $app */
use MXRVX\Telegram\Bot\Auth\Controllers\Web;
use Slim\Routing\RouteCollectorProxy;

$group = $app->group(
    '/api/connector/web',
    static function (RouteCollectorProxy $group) {
        $group->group('/auth', function (RouteCollectorProxy $group) {
            $group->any('/config/', Web\Auth\Config::class);
            $group->any('/login/', Web\Auth\Login::class);
            $group->any('/logout/', Web\Auth\Logout::class);
        });
    }
);
