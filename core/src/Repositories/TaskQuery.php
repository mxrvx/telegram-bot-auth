<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Repositories;

use Cycle\ActiveRecord\Query\ActiveQuery;
use MXRVX\Telegram\Bot\Auth\Entities\Task;

/**
 * @extends ActiveQuery<Task>
 */
class TaskQuery extends ActiveQuery
{
    public function __construct()
    {
        parent::__construct(Task::class);
    }

    public function success(bool $value = true): static
    {
        return $this->where(Task::FIELD_IS_SUCCESS, '=', $value);
    }
}
