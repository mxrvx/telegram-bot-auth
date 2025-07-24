<?php

declare(strict_types=1);

/** @psalm-suppress MissingFile */
require_once MODX_CORE_PATH . 'vendor/autoload.php';

use MXRVX\Telegram\Bot\Auth\App;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;

/** @var array<array-key, array<array-key,string>|string> $_tmp */
$_tmp = [
    'menu' => [
        'index' => [
            'text' => 'TelegramBotAuth',
            'description' => '',
        ],
    ],
    'version' => [
        'current' => 'версия: {version}',
        'available' => 'доступна: {version}',
    ],
    'commands' => [
        'login' => [
            'text' => 'Нажмите кнопку для входа на сайт',
            'button' => 'Войти',
        ],
        'login::success' => [
            'text' => 'Вход на сайт успешно выполнен, вернитесь в браузер для продолжения работы',
        ],
        'login::failure' => [
            'text' => 'Вход на сайт не удалось выполнить',
            'button' => 'Начать заново',
        ],


        'login::phone::query' => [
            'text' => 'Нажмите на кнопку *Отправить* для отправки номера телефона',
            'button' => 'Отправить',
        ],
        'login::email::query' => [
            'text' => 'Введите электронную почту',
            'button' => 'Отправить',
        ],
        'login::email::confirm' => [
            'text' => 'Подтвердите электронную почту',
            'button' => [
                'yes' => 'Да',
                'no' => 'Нет',
            ],
            'placeholder' => '{email} - принадлежит вам?',
        ],
        'login::email::code::send' => [
            'email' => [
                'subject' => 'Подтверждение email',
                'body' => 'Для подтверждения email введите код: {code}',
            ],
        ],
        'login::email::code::query' => [
            'text' => 'Введите код подтверждения отправленный вам на email',
            'button' => 'Отправить',
        ],

        'login::fullname::query' => [
            'text' => 'Укажите ФИО для регистрации',
            'button' => 'Отправить',
            'surname' => [
                'placeholder' => 'Введите Фамилию',
            ],
            'name' => [
                'placeholder' => 'Введите Имя',
            ],
            'patronymic' => [
                'placeholder' => 'Введите Отчество',
            ],
        ],
        'login::fullname::confirm' => [
            'text' => 'Подтвердите ФИО',
            'button' => [
                'yes' => 'Да',
                'no' => 'Нет',
            ],
            'placeholder' => '{fullname} - указано верно?',
        ],


        'logout' => [
            'text' => 'Нажмите кнопку для выхода с сайта',
            'button' => 'Выйти',
        ],
        'logout::success' => [
            'text' => 'Выход с сайта успешно завершен, вернитесь в браузер для продолжения работы',
        ],
        'logout::failure' => [
            'text' => 'Выход с сайта не удалось выполнить',
            'button' => 'Начать заново',
        ],

    ],
    'errors' => [
        'action' => [
            'link' => 'Ошибка получения URL авторизации',
        ],
        'window' => [
            'open' => 'Ошибка - браузер заблокировал открытие нового окна. Пожалуйста, разрешите всплывающие окна для этого сайта',
        ],
    ],
];

/** @var array<array-key, string> $_tmp */
$_tmp = Lexicon::make($_tmp, App::NAMESPACE);

/** @var array<array-key, string> $_lang */
if (isset($_lang)) {
    $_lang = \array_merge($_lang, $_tmp);
}

unset($_tmp);
