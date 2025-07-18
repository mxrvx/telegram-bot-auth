<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractIndexCommand;

/** @psalm-suppress PropertyNotSetInConstructor */
class IndexCommand extends AbstractIndexCommand
{
    protected $name = 'login::index';
    protected $description = 'Вход: запуск команд входа на сайт';
    protected $usage = '/login::index';

    public function getCommandsToExecute(): \Generator
    {
        yield PhoneQueryCommand::class;
        yield PhoneValidateCommand::class;
        yield EmailSetCommand::class;
        yield EmailQueryCommand::class;
        yield EmailConfirmCommand::class;

        if ($this->app->config->getSetting('fullname_required')?->getBoolValue()) {
            yield FullNameQueryCommand::class;
            yield FullNameConfirmCommand::class;
            yield FullNameValidateCommand::class;
        }

        yield UserSetCommand::class;
        yield SuccessCommand::class;
        yield FailureCommand::class;
    }

    public function getExecuteData(array $data = []): ?array
    {
        $this->removeCallbackMessage();

        return parent::runChain($data);
    }
}
