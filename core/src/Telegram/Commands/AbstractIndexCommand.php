<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands;

use MXRVX\Telegram\Bot\Auth\Entities\User;
use MXRVX\Telegram\Bot\Auth\Repositories\TaskRepository;

/** @psalm-suppress PropertyNotSetInConstructor */
abstract class AbstractIndexCommand extends Command
{
    protected User $user;

    public function shouldState(): bool
    {
        return false;
    }

    /**
     * @return iterable<class-string<Command>>
     */
    public function getCommandsToExecute(): iterable
    {
        yield from [];
    }

    public function runChain(array $data = []): ?array
    {
        foreach ($this->getCommandsToExecute() as $commandClass) {
            if ($this->task->getIsSuccess()) {
                return null;
            }

            if (\class_exists($commandClass) && \is_a($commandClass, AbstractChainCommand::class, true)) {
                $result = $this->executeCommandInstance($commandClass, $data);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    public function getExecuteData(array $data = []): ?array
    {
        return $this->runChain($data);
    }

    protected function initUser(): void
    {
        $user = $this->task->User;
        if ($user === null) {
            throw new \RuntimeException('User must not be null');
        }

        $repository = new TaskRepository();
        $repository->clearHistory($this->task);

        $this->user = $user;
    }
}
