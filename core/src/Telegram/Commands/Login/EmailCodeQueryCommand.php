<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;
use MXRVX\Telegram\Bot\Auth\Tools\Caster;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;

/** @psalm-suppress PropertyNotSetInConstructor */
class EmailCodeQueryCommand extends AbstractChainCommand
{
    protected $name = 'login::email::code::query';
    protected $description = 'Вход: запросить код подтверждения почты пользователя';
    protected $usage = '/login::email::code::query';

    public function shouldRun(): bool
    {
        return !$this->task->getIsSuccess() && !$this->user->getIsValidEmail();
    }

    public function shouldState(): bool
    {
        return true;
    }

    public function onUpdateState(): void
    {
        /** @psalm-suppress DocblockTypeContradiction */
        $input = Caster::string($this->update->getMessage()?->getText());
        $code = (string) ($this->task->getCommandData($this->getCommandAlias(EmailCodeSendCommand::class))['code'] ?? '');
        if ($input === $code) {
            $this->task->User?->setIsValidEmail(true);
            $this->task->saveOrFail();
        }
    }

    public function getExecuteData(array $data = []): ?array
    {
        $this->removeCallbackMessage();

        return $data +
            [
                'text' => Lexicon::item(':commands.login::email::code::query.text'),
                'callback_data' => $this->getCallbackData(IndexCommand::class),
                'reply_markup' => (new Keyboard(
                    (new KeyboardButton(
                        Lexicon::item(':commands.login::email::code::query.button'),
                    )),
                ))
                    ->setOneTimeKeyboard(true)
                    ->setResizeKeyboard(true)
                    //->setIsPersistent(FALSE)
                    ->setSelective(true),
                // ->setInputFieldPlaceholder(Lexicon::item(':commands.login::email::query.placeholder')),
            ];
    }
}
