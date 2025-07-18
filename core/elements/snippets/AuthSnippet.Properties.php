<?php

declare(strict_types=1);

/**
 * AuthSnippet Properties
 */

return [
    'tpl' => [
        'name' => 'tpl',
        'desc' => '',
        'type' => 'textfield',
        'value' => '@INLINE ' .
            <<<'HTML'
            <div x-data="mxrvxTelegramBotAuth_auth">
                <div x-show="loading" class="preloader">
                    <span class="spinner_ skeleton"></span>
                </div>
                <div x-cloak x-show="!loading">
                    <div x-show.important="!user.data.id">
                        <button class="btn btn-primary" @click="login">Войти</button>
                    </div>
                    <div x-show.important="user.data.id">
                        <button class="btn btn-primary" @click="logout">Выйти</button>
                    </div>
                </div>
            </div>
            HTML,
        'lexicon' => '',
    ],
    'login_resource_id' => [
        'name' => 'loginResourceId',
        'desc' => '',
        'type' => 'textfield',
        'value' => '',
        'lexicon' => '',
    ],
    'logout_resource_id' => [
        'name' => 'logoutResourceId',
        'desc' => '',
        'type' => 'textfield',
        'value' => '',
        'lexicon' => '',
    ],

    'return' => [
        'name' => 'return',
        'desc' => '',
        'type' => 'textfield',
        'value' => '',
        'lexicon' => '',
    ],
    'pls' => [
        'name' => 'pls',
        'desc' => '',
        'type' => 'textfield',
        'value' => '{}',
        'lexicon' => '',
    ],
    'without_css' => [
        'name' => 'without_css',
        'desc' => '',
        'type' => 'combo-boolean',
        'value' => 'false',
        'lexicon' => '',
    ],
];
