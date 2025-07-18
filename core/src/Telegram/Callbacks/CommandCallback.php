<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Callbacks;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use MXRVX\Telegram\Bot\App as BotApp;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\Command;
use MXRVX\Telegram\Bot\Auth\Entities\Task;
use MXRVX\Telegram\Bot\Auth\Services\TaskCommandManager;
use MXRVX\Telegram\Bot\Auth\Telegram\Entities\Payload;
use MXRVX\Telegram\Bot\Auth\Tools\Telegram;
use MXRVX\Telegram\Bot\Callback;
use MXRVX\Telegram\Bot\Exceptions\CommandNothingToHandleException;

class CommandCallback extends Callback
{
    protected \modX $modx;
    protected Payload $payload;
    protected Task $task;

    public function __construct(protected BotApp $botApp, protected Update $update)
    {
        parent::__construct($botApp, $update);

        $this->modx = $botApp->modx;
        $this->payload = Telegram::getUpdatePayload($update);
        $task = $this->getTaskByPayload($this->payload);
        if (!$task) {
            throw new CommandNothingToHandleException('Not found task to complete');
        }

        $this->task = $task;
    }

    public function execute(): ?ServerResponse
    {
        try {
            if ($commandClass = $this->getCommandClass()) {
                /** @var Command $commandInstance */
                $commandInstance = new $commandClass($this->botApp, $this->update, $this->payload, $this->task);
                /** @var ServerResponse|null $response */
                return $this->botApp->executeCommandInstance($commandInstance, $this->update);
            }
        } catch (\Throwable $e) {
            $this->modx->log(\modX::LOG_LEVEL_ERROR, \sprintf("\nError: %s\nFile: %s", $e->getMessage(), $e->getFile()));
        }

        return null;
    }

    /**
     * @return class-string<Command>|null
     */
    public function getCommandClass(): ?string
    {
        return TaskCommandManager::getCommandClass($this->payload, $this->task);
    }

    public function getCommandAlias(): ?string
    {
        return TaskCommandManager::getCommandAlias($this->payload, $this->task);
    }

    private function getTaskByPayload(Payload $payload): ?Task
    {
        if ($payload->uuid) {
            return Task::findOne([Task::FIELD_UUID => $payload->uuid]);
        }

        if ($payload->user) {
            return Task::findOne([Task::FIELD_TELEGRAM_ID => $payload->user]);
        }

        return null;
    }
}
