<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Repositories;

use MXRVX\Telegram\Bot\Auth\Entities\Task;

interface TaskRepositoryInterface
{
    public function findOneByUuid(string $uuid): ?Task;

    public function findOneByTelegramId(int $telegram_id): ?Task;

    public function clearHistory(Task $task): void;
}
