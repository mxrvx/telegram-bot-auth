<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;
use MXRVX\Telegram\Bot\Auth\Telegram\Entities\InlineKeyboard;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;
use MXRVX\Telegram\Bot\Auth\Tools\Telegram;

/** @psalm-suppress PropertyNotSetInConstructor */
class EmailConfirmCommand extends AbstractChainCommand
{
    protected $name = 'login::email::confirm';
    protected $description = 'Вход: подтвердить почту пользователя';
    protected $usage = '/login::email::confirm';

    public function shouldRun(): bool
    {
        return !$this->task->getIsSuccess() && $this->user->getEmail() && !$this->user->getIsValidEmail();
    }

    public function shouldState(): bool
    {
        return true;
    }

    public function getExecuteData(array $data = []): ?array
    {
        $buttons = [
            [
                'text' => Lexicon::item(':commands.login::email::confirm.button.yes'),
                'callback_data' => $this->getCallbackData(EmailCodeSendCommand::class),
            ],
            [
                'text' => Lexicon::item(':commands.login::email::confirm.button.no'),
                'callback_data' => $this->getCallbackData(EmailUnlinkCommand::class),
            ],
        ];

        $maskEmail = Telegram::maskEmail(\mb_strtoupper($this->user->getEmail(), 'UTF-8'));

        return $data + [
            'text' => \implode(PHP_EOL . PHP_EOL, [
                Lexicon::item(':commands.login::email::confirm.text'),
                Lexicon::item(':commands.login::email::confirm.placeholder', ['email' => $maskEmail]),
            ]),
            'reply_markup' => InlineKeyboard::create($buttons),
        ];
    }
}
