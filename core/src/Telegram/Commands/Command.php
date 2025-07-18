<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands;

use Longman\TelegramBot\Entities\Entity;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use MXRVX\Telegram\Bot\App as BotApp;
use MXRVX\Telegram\Bot\Auth\App;
use MXRVX\Telegram\Bot\Auth\Entities\Task;
use MXRVX\Telegram\Bot\Auth\Services\TaskCommandManager;
use MXRVX\Telegram\Bot\Auth\Services\TaskEventSender;
use MXRVX\Telegram\Bot\Auth\Telegram\Entities\Payload;
use MXRVX\Telegram\Bot\Exceptions\CommandNothingToHandleException;

/** @psalm-suppress PropertyNotSetInConstructor */
abstract class Command extends \Longman\TelegramBot\Commands\UserCommand
{
    protected \modX $modx;
    protected Payload $payload;
    protected Task $task;
    protected App $app;

    final public function __construct(BotApp $botApp, Update $update, Payload $payload, Task $task)
    {
        parent::__construct($botApp, $update);

        $this->modx = $botApp->modx;
        $this->payload = $payload;
        $this->task = $task;

        /** @var \DI\Container $container */
        $container = \MXRVX\Autoloader\App::container();
        /** @var App $this->app */
        $this->app = $container->get(App::class);

        $this->initUser();
    }

    abstract public function shouldState(): bool;

    public function onUpdateState(): void {}

    /**
     * Execute command
     *
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $data = [];
        try {
            $data = $this->getExecuteData();
        } catch (\Throwable $e) {

            $this->modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s File: %s', $e->getMessage(), $e->getFile()));
        }

        if (empty($data)) {
            throw new CommandNothingToHandleException('Nothing to reply');
        }

        try {
            $response = $this->replyToChat('', $data);
        } catch (\Throwable $e) {
            $this->modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s File: %s', $e->getMessage(), $e->getFile()));

            throw new CommandNothingToHandleException('Nothing to reply');
        }

        return $response;
    }

    public function postExecute(Command $command, null|array|ServerResponse $response): void
    {
        $commandName = $this->getCallbackAliasByCallbackClass(\get_class($command));

        if ($command->shouldState()) {
            $this->task->setCommand($commandName)->saveOrFail();
        }
    }

    /**
     * @param class-string $commandClass
     */
    public function executeCommandInstance(string $commandClass, array $data = []): ?array
    {
        if (\class_exists($commandClass) && \is_a($commandClass, self::class, true)) {
            try {
                $command = new $commandClass($this->getTelegram(), $this->update, $this->payload, $this->task);
                if (!$command->isEnabled()) {
                    return null;
                }

                if ($command instanceof AbstractChainCommand && !$command->shouldRun()) {
                    return null;
                }

                $result = $command->getExecuteData($data);
                $this->postExecute($command, $result);

                return $result;
            } catch (\Throwable $e) {
                $this->modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s File: %s', $e->getMessage(), $e->getFile()));
            }
        }

        return null;
    }

    /**
     * Helper to reply to a chat directly.
     *
     * @throws TelegramException
     */
    public function replyToChat(string $text, array $data = []): ServerResponse
    {
        $text = Entity::escapeMarkdownV2($text);

        if ($message = $this->getMessage() ?: $this->getEditedMessage() ?: $this->getChannelPost() ?: $this->getEditedChannelPost()) {
            $reply = [
                'chat_id' => $message->getChat()->getId(),
                'text' => $text,
            ];

            if ($message->getIsTopicMessage()) {
                $reply['message_thread_id'] = $message->getMessageThreadId();
            }

            return Request::sendMessage(\array_merge($reply, $data));
        } elseif ($callbackQuery = $this->getCallbackQuery()) {
            $reply = [
                'chat_id' => $callbackQuery->getFrom()->getId(),
                'text' => $text,
            ];

            return Request::sendMessage(\array_merge($reply, $data));
        }

        return Request::emptyResponse();
    }

    public function removeCallbackMessage(): void
    {
        if ($callbackQuery = $this->getCallbackQuery()) {
            Request::deleteMessage([
                'chat_id' => $callbackQuery->getFrom()->getId(),
                'message_id' => $callbackQuery->getMessage()->getMessageId(),
            ]);
        }
    }

    public function removeCallbackKeyboard(): void
    {
        if ($callbackQuery = $this->getCallbackQuery()) {
            Request::editMessageReplyMarkup([
                'chat_id' => $callbackQuery->getFrom()->getId(),
                'message_id' => $callbackQuery->getMessage()->getMessageId(),
                'reply_markup' => new InlineKeyboard([]),
            ]);
        }
    }

    public function fireTaskEvent(array $data = []): bool
    {
        try {
            $result = (new TaskEventSender($this->task))->send($data);
        } catch (\Throwable $e) {
            $this->modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s File: %s', $e->getMessage(), $e->getFile()));
            $result = false;
        }

        return $result;
    }

    abstract public function getExecuteData(array $data = []): ?array;

    /**
     * @param class-string<Command>|null $className
     */
    public function getCommandAlias(?string $className = null): string
    {
        return $this->getCallbackAliasByCallbackClass($className ?? static::class);
    }

    /**
     * @param class-string<Command> $className
     */
    public function getCallbackAliasByCallbackClass(string $className): string
    {
        if ($aliasName = TaskCommandManager::getAliasByCommandClass($className)) {
            return $aliasName;
        }
        return '';
    }

    /**
     * @param class-string<Command> $className
     */
    public function getCallbackData(string $className, null|int|string $action = null): string
    {
        $data = [$this->getCallbackAliasByCallbackClass($className), $this->task->uuid];
        if (!\is_null($action)) {
            $data[] = $action;
        }
        $callbackData = \implode(Payload::CALLBACK_SEPARATOR, $data);
        if (\strlen($callbackData) > 64) {
            $this->modx->log(\modX::LOG_LEVEL_ERROR, \sprintf("\nCallback data too long. Length: %d, \nvalue: %s", \strlen($callbackData), $callbackData));
            throw new CommandNothingToHandleException('Nothing to reply');
        }

        return $callbackData;
    }

    protected function initUser(): void {}
}
