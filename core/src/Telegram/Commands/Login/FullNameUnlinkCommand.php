<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;

/** @psalm-suppress PropertyNotSetInConstructor */
class FullNameUnlinkCommand extends AbstractChainCommand
{
    protected $name = 'login::fullname::unlink';
    protected $description = 'Вход: отвязать Полное имя пользователя';
    protected $usage = '/login::fullname::unlink';

    public function shouldRun(): bool
    {
        return !$this->task->getIsSuccess();
    }

    public function getExecuteData(array $data = []): ?array
    {
        $this->user
            ->setSurname('')
            ->setName('')
            ->setPatronymic('');

        $this->task
            ->setCommandData(
                $this->getCommandAlias(FullNameQueryCommand::class),
                [],
            );

        $this->task->saveOrFail();

        return $this->executeCommandInstance(FullNameQueryCommand::class);
    }
}
