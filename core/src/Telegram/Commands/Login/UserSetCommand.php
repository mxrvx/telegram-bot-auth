<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use MXRVX\Telegram\Bot\Auth\Repositories\ModxUserRepository;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;

/** @psalm-suppress PropertyNotSetInConstructor */
class UserSetCommand extends AbstractChainCommand
{
    protected $name = 'login::user::set';
    protected $description = 'Вход: установка пользователя';
    protected $usage = '/login::user::set';

    public function shouldRun(): bool
    {
        return !$this->task->getIsSuccess() && !$this->user->getUserId() && !$this->user->getIsValid() && $this->user->getIsValidPhone() && $this->user->getIsValidEmail();
    }

    public function getExecuteData(array $data = []): ?array
    {
        $repository = new ModxUserRepository();

        $modxUser = $repository->createOrUpdate($this->user);
        $modxUser->saveOrFail();

        $this->user->setUserId($modxUser->id)->setIsValid(true);
        $this->task->saveOrFail();

        return null;
    }
}
