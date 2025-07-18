<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Logout;

use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractIndexCommand;

/** @psalm-suppress PropertyNotSetInConstructor */
class IndexCommand extends AbstractIndexCommand
{
    protected $name = 'logout::index';
    protected $description = 'Выход: запуск команд выхода с сайта';
    protected $usage = '/logout::index';

    public function getCommandsToExecute(): \Generator
    {
        yield SuccessCommand::class;
        yield FailureCommand::class;
    }

    public function getExecuteData(array $data = []): ?array
    {
        $this->removeCallbackMessage();

        return parent::runChain($data);
    }
}
