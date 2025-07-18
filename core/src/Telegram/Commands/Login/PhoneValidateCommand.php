<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use MXRVX\Telegram\Bot\Auth\Repositories\ModxUserRepository;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;

/** @psalm-suppress PropertyNotSetInConstructor */
class PhoneValidateCommand extends AbstractChainCommand
{
    protected $name = 'login::phone::validate';
    protected $description = 'Вход: проверка телефонного номера пользователя';
    protected $usage = '/login::phone::validate';

    public function shouldRun(): bool
    {
        return !$this->task->getIsSuccess() && $this->user->getPhone() && !$this->user->getIsValidPhone();
    }

    public function getExecuteData(array $data = []): ?array
    {
        $repository = new ModxUserRepository();
        if ($modxUser = $repository->getUserByPhone($this->user->getPhone())) {
            if ($modxUser->active === false || $modxUser->Profile?->blocked === true) {
                $this->user->setIsValidPhone(false);
            } else {
                $this->user->setIsValidPhone(true);
            }
        } else {
            $this->user->setIsValidPhone(true);
        }

        $this->task->saveOrFail();

        return null;
    }
}
