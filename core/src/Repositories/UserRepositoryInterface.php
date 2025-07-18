<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Repositories;

use MXRVX\Telegram\Bot\Auth\Entities\User;

interface UserRepositoryInterface
{
    public function makeUser(int $id, ?string $username): User;

    public function findOneById(int $id): ?User;

    public function findOneByUserId(int $user_id): ?User;
}
