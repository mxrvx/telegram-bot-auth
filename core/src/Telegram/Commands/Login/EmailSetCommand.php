<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use MXRVX\Telegram\Bot\Auth\Repositories\ModxUserRepository;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;

/** @psalm-suppress PropertyNotSetInConstructor */
class EmailSetCommand extends AbstractChainCommand
{
    protected $name = 'login::email::set';
    protected $description = 'Вход: установка почты пользователя';
    protected $usage = '/login::email::set';

    public function shouldRun(): bool
    {
        return !$this->task->getIsSuccess() && $this->user->getIsValidPhone() && !$this->user->getEmail();
    }

    public function getExecuteData(array $data = []): ?array
    {
        $repository = new ModxUserRepository();
        if ($modxUser = $repository->getUserByPhone($this->user->getPhone())) {
            $this->user->setEmail($modxUser->Profile?->email ?? '');
        }

        $this->task->saveOrFail();

        return null;
    }
}
