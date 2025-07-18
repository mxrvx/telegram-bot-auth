<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Entities;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Entity\Behavior;
use MXRVX\ORM\AR\AR;
use MXRVX\Telegram\Bot\Auth\Repositories\TaskQuery;

/**
 * @psalm-type MetaData array{
 * session_id: string,
 * uuid: string,
 * telegram_id: int,
 * command: string,
 * is_success: bool,
 * config: ?array,
 * data: ?array,
 * }
 *
 * @psalm-suppress MissingConstructor
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[Entity(
    role: 'mxrvx-telegram-bot-auth:Task',
    table: 'mxrvx_telegram_bot_auth_tasks',
)]
#[Behavior\UpdatedAt(field: 'updated_at', column: 'updated_at')]
#[Index(columns: ['session_id'], unique: true, name: 'session_id')]
#[Index(columns: ['uuid'], unique: true, name: 'uuid')]
#[Index(columns: ['telegram_id'], name: 'telegram_id')]
#[Index(columns: ['session_id'], name: 'session_id')]
#[Index(columns: ['command'], name: 'command')]
#[Index(columns: ['updated_at'], name: 'updated_at')]
class Task extends AR implements TaskMetaData
{
    #[Column(type: 'string(191)', primary: true, typecast: 'string')]
    public string $session_id = '';

    #[Column(type: 'string(36)')]
    public string $uuid = '';

    #[Column(type: 'bigInteger', typecast: 'int', unsigned: true, nullable: true)]
    public ?int $telegram_id = null;

    #[Column(type: 'string(191)', default: '', typecast: 'string')]
    public string $command = '';

    #[Column(type: 'boolean', typecast: 'bool', default: false)]
    public bool $is_success = false;

    #[Column(type: 'json', typecast: 'json', nullable: true)]
    public ?array $config = null;

    #[Column(type: 'json', typecast: 'json', nullable: true)]
    public ?array $data = null;

    #[Column(type: 'datetime', nullable: true)]
    public ?\DateTimeImmutable $updated_at = null;

    #[BelongsTo(target: User::class, innerKey: 'telegram_id', outerKey: 'id', nullable: true)]
    public ?User $User = null;

    public static function query(): TaskQuery
    {
        return new TaskQuery();
    }

    public function getSessionId(): string
    {
        return $this->session_id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getTelegramId(): int
    {
        return $this->telegram_id ?? 0;
    }

    public function getCommand(): ?string
    {
        return empty($this->command) ? null : $this->command;
    }

    public function getIsSuccess(): bool
    {
        return $this->is_success;
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getCommandData(string $command): array
    {
        /** @var mixed $value */
        $value = $this->data[$command] ?? [];
        return \is_array($value) ? $value : [];
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setSessionId(string $value): static
    {
        $this->session_id = $value;
        return $this;
    }

    public function setUuid(string $value): static
    {
        $this->uuid = $value;
        return $this;
    }

    public function setTelegramId(int $value): static
    {
        $this->telegram_id = $value;
        return $this;
    }

    public function setCommand(string $value): static
    {
        $this->command = $value;
        return $this;
    }

    public function setIsSuccess(bool $value): static
    {
        $this->is_success = $value;
        return $this;
    }

    public function setConfig(array $value): static
    {
        $this->config = $value;
        return $this;
    }

    public function setData(?array $value): static
    {
        $this->data = $value;
        return $this;
    }

    public function setCommandData(string $command, array $value): static
    {
        $this->data[$command] = $value;
        return $this;
    }

    public function reset(): static
    {
        return $this->setCommand('')->setIsSuccess(false)->setData(null);
    }
}
