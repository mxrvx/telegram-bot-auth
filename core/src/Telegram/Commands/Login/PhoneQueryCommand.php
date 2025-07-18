<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;
use MXRVX\Telegram\Bot\Auth\Tools\Caster;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;

/** @psalm-suppress PropertyNotSetInConstructor */
class PhoneQueryCommand extends AbstractChainCommand
{
    protected $name = 'login::phone::query';
    protected $description = 'Вход: запросить телефон пользователя';
    protected $usage = '/login::phone::query';

    public function shouldRun(): bool
    {
        return !$this->task->getIsSuccess() && !$this->user->getPhone();
    }

    public function shouldState(): bool
    {
        return true;
    }

    public function onUpdateState(): void
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if ($phone = $this->update->getMessage()?->getContact()?->getPhoneNumber()) {
            $this->task->User?->setPhone(Caster::phone($phone));
            $this->task->saveOrFail();
        }
    }

    public function getExecuteData(array $data = []): ?array
    {
        return $data +
            [
                'text' => Lexicon::item(':commands.login::phone::query.text'),
                'callback_data' => $this->getCallbackData(IndexCommand::class),
                'reply_markup' => (new Keyboard(
                    (new KeyboardButton(
                        Lexicon::item(':commands.login::phone::query.button'),
                    ))->setRequestContact(true),
                ))
                    ->setOneTimeKeyboard(true)
                    ->setResizeKeyboard(true)
                    ->setSelective(true),
            ];
    }
}
