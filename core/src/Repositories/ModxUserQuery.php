<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Repositories;

use Cycle\ActiveRecord\Query\ActiveQuery;
use MXRVX\ORM\MODX\Entities\User as ModxUser;

/**
 * @extends ActiveQuery<ModxUser>
 */
class ModxUserQuery extends ActiveQuery
{
    public function __construct()
    {
        parent::__construct(ModxUser::class);
    }
}
