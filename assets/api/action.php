<?php

use MXRVX\ORM\MODX\Entities\Resource;
use MXRVX\Telegram\Bot\Auth\App;
use MXRVX\Telegram\Bot\Auth\Repositories\TaskRepository;
use MXRVX\Telegram\Bot\Auth\Services\TaskCommandManager;
use MXRVX\Telegram\Bot\Auth\Tools\Caster;

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

$repository = new TaskRepository();
$task = $repository->findOneByUuid(Caster::uuid($_GET['uuid'] ?? ''));
if (!$task || !$task?->getIsSuccess()) {
    $modx->sendRedirect(MODX_SITE_URL);
    exit;
}

$config = $task->getConfig();
$action = strtolower(TaskCommandManager::getCommandAction($task->getCommand()));
$context = $config['context'] ?? 'web';

if (!$resourceId = $config[$action . '_resource_id'] ?? NULL) {
    if ($resource = Resource::findOne(['context_key' => $context, 'deleted' => FALSE, 'published' => TRUE])) {
        $resourceId = $resource->id;
        $context = $resource->context_key;
    }
};

$modx->initialize($context);

if ($task?->getSessionId() !== session_id()) {
    $modx->sendRedirect(MODX_SITE_URL);
    exit;
}

$url = $modx->makeUrl(
    $resourceId,
    $context,
    [
        'service' => App::NAMESPACE,
        'action' => $action,
    ],
    'full',
    [
        'xhtml_urls' => FALSE,
    ]);
$url = empty($url) ? MODX_SITE_URL : $url;

if ('login' === $action) {
    if ($user = $modx->getObject(\modUser::class, ['id' => $task->User?->getUserId()])) {
        $modx->user = $user;
        $modx->user->addSessionContext($context);
        $modx->getUser($context, true);
    }
}

if ('logout' === $action) {
    if ($contexts = $modx->user->getSessionContexts()) {
        foreach ($contexts as $ctx => $id) {
            $modx->user->removeSessionContext($ctx);
        }
    }
}

session_write_close();
$modx->sendRedirect($url);
exit;
