<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Entities;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Entity\Behavior;
use MXRVX\ORM\AR\AR;
use MXRVX\Telegram\Bot\Auth\Repositories\UserQuery;

/**
 * @psalm-type MetaData array{
 * id: int,
 * username: string,
 * phone: string,
 * email: string,
 * surname: string,
 * name: string,
 * patronymic: string,
 * user_id: int,
 * is_valid: bool,
 * is_valid_phone: bool,
 * is_valid_email: bool,
 * is_valid_fullname: bool
 * }
 *
 * @psalm-suppress MissingConstructor
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[Entity(
    role: 'mxrvx-telegram-bot-auth:User',
    table: 'mxrvx_telegram_bot_auth_users',
)]
#[Behavior\CreatedAt(field: 'created_at', column: 'created_at')]
#[Behavior\UpdatedAt(field: 'updated_at', column: 'updated_at')]
#[Index(columns: ['id'], unique: true, name: 'id')]
#[Index(columns: ['user_id'], name: 'user_id')]
#[Index(columns: ['phone'], name: 'phone')]
#[Index(columns: ['email'], name: 'email')]
#[Index(columns: ['created_at'], name: 'created_at')]
#[Index(columns: ['updated_at'], name: 'updated_at')]
class User extends AR implements UserMetaData
{
    #[Column(type: 'bigInteger', primary: true, typecast: 'int', unsigned: true)]
    public int $id;

    #[Column(type: 'string(191)', default: '', typecast: 'string')]
    public string $username = '';

    #[Column(type: 'string(100)', default: '', typecast: 'string')]
    public string $phone = '';

    #[Column(type: 'string(100)', default: '', typecast: 'string')]
    public string $email = '';

    #[Column(type: 'string(100)', default: '', typecast: 'string')]
    public string $surname = '';

    #[Column(type: 'string(100)', default: '', typecast: 'string')]
    public string $name = '';

    #[Column(type: 'string(100)', default: '', typecast: 'string')]
    public string $patronymic = '';

    #[Column(type: 'int', typecast: 'int', unsigned: true, default: 0)]
    public int $user_id = 0;

    #[Column(type: 'boolean', typecast: 'bool', default: false)]
    public bool $is_valid = false;

    #[Column(type: 'boolean', typecast: 'bool', default: false)]
    public bool $is_valid_phone = false;

    #[Column(type: 'boolean', typecast: 'bool', default: false)]
    public bool $is_valid_email = false;

    #[Column(type: 'boolean', typecast: 'bool', default: false)]
    public bool $is_valid_fullname = false;

    #[Column(type: 'datetime')]
    public \DateTimeImmutable $created_at;

    #[Column(type: 'datetime', nullable: true)]
    public ?\DateTimeImmutable $updated_at = null;

    public static function query(): UserQuery
    {
        return new UserQuery();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPatronymic(): string
    {
        return $this->patronymic;
    }

    public function getFullName(): ?string
    {
        $values = \array_filter([$this->surname, $this->name, $this->patronymic]);

        return \count($values) === 3 ? \implode(' ', $values) : null;
    }

    public function getIsValid(): bool
    {
        return $this->is_valid;
    }

    public function getIsValidPhone(): bool
    {
        return $this->is_valid_phone;
    }

    public function getIsValidEmail(): bool
    {
        return $this->is_valid_email;
    }

    public function getIsValidFullname(): bool
    {
        return $this->is_valid_fullname;
    }

    public function setId(int $value): static
    {
        $this->id = $value;
        return $this;
    }

    public function setUserId(int $value): static
    {
        $this->user_id = $value;
        return $this;
    }

    public function setUsername(string $value): static
    {
        $this->username = $value;
        return $this;
    }

    public function setPhone(string $value): static
    {
        $this->phone = $value;
        return $this;
    }

    public function setEmail(string $value): static
    {
        $this->email = $value;
        return $this;
    }

    public function setSurname(string $value): static
    {
        $this->surname = $value;
        return $this;
    }

    public function setName(string $value): static
    {
        $this->name = $value;
        return $this;
    }

    public function setPatronymic(string $value): static
    {
        $this->patronymic = $value;
        return $this;
    }

    public function setIsValid(bool $value): static
    {
        $this->is_valid = $value;
        return $this;
    }

    public function setIsValidEmail(bool $value): static
    {
        $this->is_valid_email = $value;
        return $this;
    }

    public function setIsValidPhone(bool $value): static
    {
        $this->is_valid_phone = $value;
        return $this;
    }

    public function setIsValidFullname(bool $value): static
    {
        $this->is_valid_fullname = $value;
        return $this;
    }

    public function reset(): static
    {
        return $this->setUserId(0)->setPhone('')->setEmail('')->setIsValid(false)->setIsValidEmail(false)->setIsValidPhone(false);
    }
}
