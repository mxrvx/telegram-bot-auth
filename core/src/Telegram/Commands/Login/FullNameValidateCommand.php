<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;

/** @psalm-suppress PropertyNotSetInConstructor */
class FullNameValidateCommand extends AbstractChainCommand
{
    protected $name = 'login::fullname::validate';
    protected $description = 'Вход: проверка Полного имени пользователя';
    protected $usage = '/login::fullname::validate';

    public function shouldRun(): bool
    {
        return $this->user->getFullName() && !$this->user->getIsValidFullname();
    }

    public function getExecuteData(array $data = []): ?array
    {
        $this->user->setIsValidFullname(true);
        $this->task->saveOrFail();

        return $this->executeCommandInstance(IndexCommand::class);
    }
}
