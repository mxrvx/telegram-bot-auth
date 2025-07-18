<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;
use MXRVX\Telegram\Bot\Auth\Telegram\Entities\InlineKeyboard;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;

/** @psalm-suppress PropertyNotSetInConstructor */
class FullNameConfirmCommand extends AbstractChainCommand
{
    protected $name = 'login::fullname::confirm';
    protected $description = 'Вход: подтвердить Полное имя пользователя';
    protected $usage = '/login::fullname::confirm';

    public function shouldRun(): bool
    {
        return $this->user->getFullName() && !$this->user->getIsValidFullname();
    }

    public function shouldState(): bool
    {
        return false;
    }

    public function getExecuteData(array $data = []): ?array
    {
        $buttons = [
            [
                'text' => Lexicon::item(':commands.login::fullname::confirm.button.yes'),
                'callback_data' => $this->getCallbackData(FullNameValidateCommand::class),
            ],
            [
                'text' => Lexicon::item(':commands.login::fullname::confirm.button.no'),
                'callback_data' => $this->getCallbackData(FullNameUnlinkCommand::class),
            ],
        ];

        return $data + [
            'text' => \implode(PHP_EOL . PHP_EOL, [
                Lexicon::item(':commands.login::fullname::confirm.text'),
                Lexicon::item(':commands.login::fullname::confirm.placeholder', ['fullname' => $this->user->getFullName() ?? '']),

            ]),
            'reply_markup' => InlineKeyboard::create($buttons),
        ];
    }
}
