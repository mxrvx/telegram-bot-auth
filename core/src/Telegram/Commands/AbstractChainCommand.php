<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands;

/** @psalm-suppress PropertyNotSetInConstructor */
abstract class AbstractChainCommand extends AbstractIndexCommand
{
    abstract public function shouldRun(): bool;
}
