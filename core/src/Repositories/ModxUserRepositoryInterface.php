<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Repositories;

use MXRVX\ORM\MODX\Entities\User as ModxUser;
use MXRVX\Telegram\Bot\Auth\Entities\User;

interface ModxUserRepositoryInterface
{
    public function makeUser(User $user): ModxUser;

    public function createOrUpdate(User $user): ModxUser;

    public function getUserById(int $id): ?ModxUser;

    public function getUserByPhone(string $phone): ?ModxUser;

    public function getUserByEmail(string $email): ?ModxUser;
}
