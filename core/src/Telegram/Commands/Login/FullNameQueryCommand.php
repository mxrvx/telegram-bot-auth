<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use MXRVX\Telegram\Bot\Auth\Entities\User;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;
use MXRVX\Telegram\Bot\Auth\Tools\Caster;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;

/** @psalm-suppress PropertyNotSetInConstructor */
class FullNameQueryCommand extends AbstractChainCommand
{
    protected $name = 'login::fullname::query';
    protected $description = 'Вход: запросить Полное имя пользователя';
    protected $usage = '/login::fullname::query';

    public function shouldRun(): bool
    {
        return !$this->user->getFullName();
    }

    public function shouldState(): bool
    {
        return true;
    }

    public function getExecuteData(array $data = []): ?array
    {
        $this->removeCallbackMessage();

        $field = match (true) {
            empty($this->user->getSurname()) => User::FIELD_SURNAME,
            empty($this->user->getName()) => User::FIELD_NAME,
            empty($this->user->getPatronymic()) => User::FIELD_PATRONYMIC,
        };

        $this->task
            ->setCommandData(
                $this->getCommandAlias(),
                [
                    'field' => $field,
                ],
            );

        $this->task->saveOrFail();

        return $data +
            [
                'text' => $field === User::FIELD_SURNAME
                    ? \implode(PHP_EOL . PHP_EOL, [
                        Lexicon::item(':commands.login::fullname::query.text'),
                        Lexicon::item(\sprintf(':commands.login::fullname::query.%s.placeholder', $field)),
                    ])
                    : Lexicon::item(\sprintf(':commands.login::fullname::query.%s.placeholder', $field)),
                'callback_data' => $this->getCallbackData(IndexCommand::class),
                'reply_markup' => (new Keyboard(
                    (new KeyboardButton(
                        Lexicon::item(':commands.login::fullname::query.button'),
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
        $input = Caster::string($this->update->getMessage()?->getText(), 100);
        if (!empty($input)) {
            $field = (string) ($this->task->getCommandData($this->getCommandAlias())['field'] ?? '');
            match ($field) {
                User::FIELD_SURNAME => $this->user->setSurname($input),
                User::FIELD_NAME => $this->user->setName($input),
                User::FIELD_PATRONYMIC => $this->user->setPatronymic($input),
            };
            $this->task->saveOrFail();
        }
    }
}
