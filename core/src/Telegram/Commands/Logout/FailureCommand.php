<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Logout;

use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\LogoutCommand;
use MXRVX\Telegram\Bot\Auth\Telegram\Entities\InlineKeyboard;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;

/** @psalm-suppress PropertyNotSetInConstructor */
class FailureCommand extends AbstractChainCommand
{
    protected $name = 'logout::failure';
    protected $description = 'Выход: ошибка';
    protected $usage = '/logout::failure';

    public function shouldRun(): bool
    {
        return !$this->task->getIsSuccess();
    }

    public function shouldState(): bool
    {
        return true;
    }

    public function getExecuteData(array $data = []): ?array
    {
        $this->removeCallbackMessage();

        $buttons = [
            [
                'text' => Lexicon::item(':commands.logout::failure.button'),
                'callback_data' => $this->getCallbackData(LogoutCommand::class),
            ],
        ];

        return $data +
            [
                'text' => Lexicon::item(':commands.logout::failure.text'),
                'reply_markup' => InlineKeyboard::create($buttons),
            ];
    }
}
