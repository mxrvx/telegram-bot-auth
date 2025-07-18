<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;

/** @psalm-suppress PropertyNotSetInConstructor */
class EmailQueryCommand extends AbstractChainCommand
{
    protected $name = 'login::email::query';
    protected $description = 'Вход: запросить почту пользователя';
    protected $usage = '/login::email::query';

    public function shouldRun(): bool
    {
        return !$this->task->getIsSuccess() && !$this->user->getEmail();
    }

    public function shouldState(): bool
    {
        return true;
    }

    public function getExecuteData(array $data = []): ?array
    {
        $this->removeCallbackMessage();

        return $data +
            [
                'text' => Lexicon::item(':commands.login::email::query.text'),
                'callback_data' => $this->getCallbackData(IndexCommand::class),
                'reply_markup' => (new Keyboard(
                    (new KeyboardButton(
                        Lexicon::item(':commands.login::email::query.button'),
                    )),
                ))
                    ->setOneTimeKeyboard(true)
                    ->setResizeKeyboard(true)
                    ->setSelective(true),
            ];
    }

    public function onUpdateState(): void
    {
        /** @psalm-suppress DocblockTypeContradiction */
        $text = $this->update->getMessage()?->getText() ?? '';
        $email = \mb_strtolower(\trim($text), 'utf-8');
        if (\preg_match('/^\S+@\S+[.]\S+$/', $email)) {
            $this->task->User?->setEmail($email);
            $this->task->saveOrFail();
        }
    }
}
