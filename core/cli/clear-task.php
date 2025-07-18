<?php

declare(strict_types=1);

/** @var \modX $modx */
/** @psalm-suppress MissingFile */
require \dirname(__DIR__) . '/autoloader.php';

use Cycle\ActiveRecord\Query\ActiveQuery;
use MXRVX\ORM\MODX\Entities\Session;
use MXRVX\Telegram\Bot\Auth\Entities\Task;
use MXRVX\Telegram\Bot\Auth\Repositories\TaskQuery;

// NOTE: delete all expired && success tasks
$builder = (new TaskQuery())->getBuilder();
$source = $builder->getLoader()->getSource();
$db = $source->getDatabase();
$db->table($source->getTable())->delete([
    Task::FIELD_IS_SUCCESS => ['=' => true],
    Task::FIELD_UPDATED_AT => ['<' => (new \DateTimeImmutable('-1 hour'))],
])->run();
$db->table($source->getTable())->delete([
    Task::FIELD_UPDATED_AT => ['<' => (new \DateTimeImmutable('-24 hour'))],
])->run();


// NOTE: delete all sessions with empty data
$builder = (new ActiveQuery(Session::class))->getBuilder();
$source = $builder->getLoader()->getSource();
$db = $source->getDatabase();
$db->table($source->getTable())->delete([
    'data' => ['=' => ''],
])->run();
