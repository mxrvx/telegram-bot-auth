<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

//use Longman\TelegramBot\Entities\InlineKeyboard;

use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;
use MXRVX\Telegram\Bot\Auth\Telegram\Entities\InlineKeyboard;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\LoginCommand;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;

/** @psalm-suppress PropertyNotSetInConstructor */
class FailureCommand extends AbstractChainCommand
{
    protected $name = 'login::failure';
    protected $description = 'Вход: ошибка';
    protected $usage = '/login::failure';

    public function shouldRun(): bool
    {
        return true;
    }

    public function shouldState(): bool
    {
        return true;
    }

    public function getExecuteData(array $data = []): ?array
    {
        $this->task->User?->reset();
        $this->task->reset()->saveOrFail();

        $buttons = [
            [
                'text' => Lexicon::item(':commands.login::failure.button'),
                'callback_data' => $this->getCallbackData(LoginCommand::class),
            ],
        ];

        return $data + [
            'text' => Lexicon::item(':commands.login::failure.text'),
            'reply_markup' => InlineKeyboard::create($buttons),
        ];
    }
}
