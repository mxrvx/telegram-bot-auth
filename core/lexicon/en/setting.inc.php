<?php

declare(strict_types=1);

/** @psalm-suppress MissingFile */
require_once MODX_CORE_PATH . 'vendor/autoload.php';

use MXRVX\Telegram\Bot\Auth\App;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;

/** @var array<array-key, array<array-key,string>|string> $_tmp */
$_tmp = [
    'grid_post_fields' => 'Поля таблицы `Посты`',
    'grid_post_fields_desc' => 'Список видимых полей таблицы `Посты`, через запятую',
];

/** @var array<array-key, string> $_tmp */
$_tmp = Lexicon::make($_tmp, 'setting_' . App::NAMESPACE);

/** @var array<array-key, string> $_lang */
if (isset($_lang)) {
    $_lang = \array_merge($_lang, $_tmp);
}

unset($_tmp);
