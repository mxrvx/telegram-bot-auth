<?php


declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Entities;

use MXRVX\Telegram\Bot\Auth\Tools\Caster;

/**
 * @psalm-type MetaData = array{
 *     user: int,
 *     command: string,
 *     action: null|int|string,
 *     uuid: string
 * }
 */
class Payload
{
    public const CALLBACK_SEPARATOR = '::';

    public function __construct(
        public readonly ?int    $user,
        public readonly ?string $uuid,
        public readonly ?string $command,
        public readonly ?string $action,
    ) {}

    public static function make(mixed $user, mixed $uuid, mixed $command, mixed $action): self
    {
        return new self(
            user: Caster::int($user) ?: null,
            uuid: Caster::uuid($uuid) ?: null,
            command: Caster::string($command) ?: null,
            action: Caster::string($action),
        );
    }

    public function getUser(): ?int
    {
        return $this->user;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'command' => $this->command,
            'action' => $this->action,
            'uuid' => $this->uuid,
        ];
    }
}
