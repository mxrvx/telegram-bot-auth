<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Repositories;

use Cycle\ActiveRecord\Repository\ActiveRepository;
use Cycle\ORM\ORMInterface;
use MXRVX\Telegram\Bot\Auth\Entities\User;

/**
 * @method UserQuery select()
 * @extends ActiveRepository<User>
 *
 * @psalm-suppress InternalClass
 * @psalm-suppress InternalMethod
 */
final class UserRepository extends ActiveRepository implements UserRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(User::class);
    }

    #[\Override]
    public function initSelect(ORMInterface $orm, string $role): UserQuery
    {
        return new UserQuery();
    }

    public function makeUser(int $id, ?string $username): User
    {
        return User::make([
            User::FIELD_ID => $id,
            User::FIELD_USERNAME => $username ?? $id,
        ]);
    }

    public function findOneById(int $id): ?User
    {
        return $this->select()->where(User::FIELD_ID, $id)->fetchOne() ?? null;
    }

    public function findOneByUserId(int $user_id): ?User
    {
        return $this->select()->where(User::FIELD_USER_ID, $user_id)->fetchOne() ?? null;
    }
}
