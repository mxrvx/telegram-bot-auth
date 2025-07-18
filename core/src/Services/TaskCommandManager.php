<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Services;

use MXRVX\Telegram\Bot\Auth\Controllers\Controller;
use MXRVX\Telegram\Bot\Auth\Entities\Task;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\Command;
use MXRVX\Telegram\Bot\Auth\Telegram\Entities\Payload;
use MXRVX\Telegram\Bot\Auth\Tools\Caster;

class TaskCommandManager
{
    public const COMMAND_BASE_NAMESPACE = 'MXRVX\\Telegram\\Bot\\Auth\\Telegram\\Commands\\';
    public const COMMAND_CLASS_SUFFIX = 'Command';

    /**
     * @return class-string<Command>|null
     */
    public static function getCommandClassByAlias(mixed $aliasName): ?string
    {
        $aliasName = Caster::string($aliasName, 0);
        $className = self::COMMAND_BASE_NAMESPACE . $aliasName . self::COMMAND_CLASS_SUFFIX;
        if (\class_exists($className) && \is_a($className, Command::class, true)) {
            return $className;
        }

        return null;
    }

    /**
     * @param class-string<Command> $className
     */
    public static function getAliasByCommandClass(string $className): ?string
    {
        if (\str_starts_with($className, self::COMMAND_BASE_NAMESPACE)) {
            $aliasName = \substr($className, \strlen(self::COMMAND_BASE_NAMESPACE));
        } else {
            return null;
        }

        if (\str_ends_with($aliasName, self::COMMAND_CLASS_SUFFIX)) {
            $aliasName = \substr($aliasName, 0, -\strlen(self::COMMAND_CLASS_SUFFIX));
        }

        return $aliasName;
    }

    /**
     * @param class-string<Command|Controller> $className
     */
    public static function getAliasByActionClass(string $className): ?string
    {
        return \basename(\str_replace('\\', '/', $className));
    }

    /**
     * @return class-string<Command>|null
     */
    public static function getPayloadCommandClass(Payload $payload): ?string
    {
        if (!$aliasName = $payload->getCommand()) {
            return null;
        }

        if ($className = self::getCommandClassByAlias($aliasName)) {
            return $className;
        }

        return null;
    }

    public static function getPayloadCommandAlias(Payload $payload): ?string
    {
        if (!$aliasName = $payload->getCommand()) {
            return null;
        }

        if (!$className = self::getCommandClassByAlias($aliasName)) {
            return null;
        }

        return self::getAliasByCommandClass($className);
    }

    public static function getCommandAction(string $aliasName): string
    {
        if ($pos = \strpos($aliasName, '\\')) {
            $aliasName = \substr($aliasName, 0, $pos);
        }

        return $aliasName;
    }

    /**
     * @return class-string<Command>|null
     */
    public static function getTaskCommandClass(Task $task): ?string
    {
        if (!$aliasName = $task->getCommand()) {
            return null;
        }

        if (\str_contains($aliasName, '\\')) {
            $aliasName = \sprintf('%s\\%s', self::getCommandAction($aliasName), 'Index');
        }

        if ($className = self::getCommandClassByAlias($aliasName)) {
            return $className;
        }

        return null;
    }

    public static function getTaskCommandAlias(Task $task): ?string
    {
        if (!$aliasName = $task->getCommand()) {
            return null;
        }

        if (!$className = self::getCommandClassByAlias($aliasName)) {
            return null;
        }

        return self::getAliasByCommandClass($className);
    }

    /**
     * @return class-string<Command>|null
     */
    public static function getCommandClass(Payload $payload, Task $task): ?string
    {
        if ($className = self::getPayloadCommandClass($payload)) {
            return $className;
        }

        if ($className = self::getTaskCommandClass($task)) {
            return $className;
        }

        return null;
    }

    public static function getCommandAlias(Payload $payload, Task $task): ?string
    {
        if ($aliasName = self::getPayloadCommandAlias($payload)) {
            return $aliasName;
        }

        if ($aliasName = self::getTaskCommandAlias($task)) {
            return $aliasName;
        }

        return null;
    }
}
