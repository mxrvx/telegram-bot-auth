<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Logout;

use MXRVX\Telegram\Bot\Auth\Repositories\ModxUserRepository;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;

/** @psalm-suppress PropertyNotSetInConstructor */
class SuccessCommand extends AbstractChainCommand
{
    protected $name = 'logout::success';
    protected $description = 'Выход: успех';
    protected $usage = '/logout::success';

    public function shouldRun(): bool
    {
        return $this->user->getUserId() && $this->user->getIsValid();
    }

    public function shouldState(): bool
    {
        return true;
    }

    public function getExecuteData(array $data = []): ?array
    {
        $repository = new ModxUserRepository();

        $modxUser = $repository->getUserById($this->user->getUserId());
        if (!$modxUser || $modxUser->active === false || $modxUser->Profile?->blocked === true) {
            $this->user->reset();
            $this->task->saveOrFail();
            return null;
        }

        $this->task->setIsSuccess(true)->saveOrFail();

        $this->fireTaskEvent();

        return $data + [
            'text' => Lexicon::item(':commands.logout::success.text'),
            'callback_data' => $this->getCallbackData(static::class),
        ];
    }
}
