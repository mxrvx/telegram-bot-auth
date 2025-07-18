<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Listeners;

use Longman\TelegramBot\Entities\Update;
use MXRVX\Telegram\Bot\App as BotApp;
use MXRVX\Telegram\Bot\Auth\Entities\Task;
use MXRVX\Telegram\Bot\Auth\Repositories\TaskRepository;
use MXRVX\Telegram\Bot\Auth\Services\TaskCommandManager;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\Command;
use MXRVX\Telegram\Bot\Auth\Telegram\Entities\Payload;
use MXRVX\Telegram\Bot\Auth\Tools\Telegram;
use MXRVX\Telegram\Bot\Exceptions\ListenerNothingToHandleException;
use MXRVX\Telegram\Bot\Listeners\UserListener;

class Listener extends UserListener
{
    protected Payload $payload;
    protected Task $task;

    public function __construct(protected BotApp $botApp, protected Update $update)
    {
        parent::__construct($botApp, $update);

        $payload = Telegram::getUpdatePayload($update);
        $repository = new TaskRepository();
        if (!$task = $payload->uuid ? $repository->findOneByUuid($payload->uuid) : null) {
            $task = $payload->user ? $repository->findOneByTelegramId($payload->user) : null;
        }

        if (!$task) {
            throw new ListenerNothingToHandleException('Task not found');
        }

        $this->payload = $payload;
        $this->task = $task;
    }

    public function execute(): void
    {
        $commandClass = $this->getCommandClassByAlias($this->task->getCommand());
        if (!$commandClass) {
            return;
        }

        if (\class_exists($commandClass) && \is_a($commandClass, Command::class, true)) {
            $command = new $commandClass($this->botApp, $this->update, $this->payload, $this->task);
            if ($command->shouldState()) {
                $command->onUpdateState();
            }
        }
    }

    /**
     * @return class-string<Command>|null
     */
    protected function getCommandClassByAlias(?string $alias): ?string
    {
        if (!$alias) {
            return null;
        }
        return TaskCommandManager::getCommandClassByAlias($alias);
    }
}
