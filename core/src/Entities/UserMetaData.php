<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Entities;

interface UserMetaData
{
    public const FIELD_ID = 'id';
    public const FIELD_USER_ID = 'user_id';
    public const FIELD_USERNAME = 'username';
    public const FIELD_PHONE = 'phone';
    public const FIELD_EMAIL = 'email';
    public const FIELD_SURNAME = 'surname';
    public const FIELD_NAME = 'name';
    public const FIELD_PATRONYMIC = 'patronymic';
    public const FIELD_IS_VALID = 'is_valid';
    public const FIELD_IS_VALID_PHONE = 'is_valid_phone';
    public const FIELD_IS_VALID_EMAIL = 'is_valid_email';
}
