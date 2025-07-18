<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;

/** @psalm-suppress PropertyNotSetInConstructor */
class EmailUnlinkCommand extends AbstractChainCommand
{
    protected $name = 'login::email::unlink';
    protected $description = 'Вход: отвязать почту пользователя';
    protected $usage = '/login::email::unlink';

    public function shouldRun(): bool
    {
        return !$this->task->getIsSuccess();
    }

    public function getExecuteData(array $data = []): ?array
    {
        $this->user->setEmail('');
        $this->task->saveOrFail();

        return $this->executeCommandInstance(EmailQueryCommand::class);
    }
}
