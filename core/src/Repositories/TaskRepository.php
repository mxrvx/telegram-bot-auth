<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Repositories;

use Cycle\ORM\ORMInterface;
use Cycle\ActiveRecord\Repository\ActiveRepository;
use MXRVX\Telegram\Bot\Auth\Entities\Task;

/**
 * @method TaskQuery select()
 * @extends ActiveRepository<Task>
 *
 * @psalm-suppress InternalClass
 * @psalm-suppress InternalMethod
 */
class TaskRepository extends ActiveRepository implements TaskRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Task::class);
    }

    #[\Override]
    public function initSelect(ORMInterface $orm, string $role): TaskQuery
    {
        return new TaskQuery();
    }

    public function findOneByUuid(string $uuid): ?Task
    {
        return $this->select()->where(Task::FIELD_UUID, $uuid)->fetchOne() ?? null;
    }

    public function findOneByTelegramId(int $telegram_id): ?Task
    {
        return $this->select()->where(Task::FIELD_TELEGRAM_ID, $telegram_id)->fetchOne() ?? null;
    }

    public function clearHistory(Task $task): void
    {
        $tasks = $this->select()
            ->where([Task::FIELD_TELEGRAM_ID => $task->getTelegramId()])
            ->where(Task::FIELD_UUID, '!=', $task->getUuid())
            ->fetchAll();
        foreach ($tasks as $task) {
            $task->delete();
        }
    }
}
