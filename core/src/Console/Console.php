<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Console;

use DI\Container;
use MXRVX\ORM\Console\Command\Migration\MigrationDownCommand;
use MXRVX\ORM\Console\Command\Migration\MigrationUpCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\ListCommand;
use MXRVX\Telegram\Bot\Auth\App;
use MXRVX\Telegram\Bot\Auth\Console\Command\InstallCommand;
use MXRVX\Telegram\Bot\Auth\Console\Command\RemoveCommand;

class Console extends Application
{
    public function __construct(protected Container $container)
    {
        parent::__construct(App::NAMESPACE);
    }

    protected function getDefaultCommands(): array
    {
        return [
            new ListCommand(),
            new InstallCommand($this->container),
            new RemoveCommand($this->container),

            new MigrationUpCommand($this->container),
            new MigrationDownCommand($this->container),
        ];
    }
}
