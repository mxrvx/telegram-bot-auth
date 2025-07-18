<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Repositories;

use Cycle\ActiveRecord\Query\ActiveQuery;
use MXRVX\Telegram\Bot\Auth\Entities\User;

/**
 * @extends ActiveQuery<User>
 */
class UserQuery extends ActiveQuery
{
    public function __construct()
    {
        parent::__construct(User::class);
    }
}
