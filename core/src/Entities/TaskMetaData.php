<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Entities;

interface TaskMetaData
{
    public const FIELD_SESSION_ID = 'session_id';
    public const FIELD_UUID = 'uuid';
    public const FIELD_TELEGRAM_ID = 'telegram_id';
    public const FIELD_COMMAND = 'command';
    public const FIELD_IS_SUCCESS = 'is_success';
    public const FIELD_CONFIG = 'config';
    public const FIELD_DATA = 'data';
    public const FIELD_UPDATED_AT = 'updated_at';
}
