<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands;

use MXRVX\Telegram\Bot\Auth\Repositories\UserRepository;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\Logout\IndexCommand;
use MXRVX\Telegram\Bot\Auth\Telegram\Entities\InlineKeyboard;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;

/** @psalm-suppress PropertyNotSetInConstructor */
class LogoutCommand extends Command
{
    protected $name = 'logout';
    protected $description = 'Выход';
    protected $usage = '/logout';

    public function shouldState(): bool
    {
        return true;
    }

    public function getExecuteData(array $data = []): ?array
    {
        $this->task->reset()->setCommand('Logout')->saveOrFail();

        $this->removeCallbackMessage();

        $buttons = [
            [
                'text' => Lexicon::item(':commands.logout.button'),
                'callback_data' => $this->getCallbackData(IndexCommand::class),
            ],
        ];

        return $data + [
            'text' => Lexicon::item(':commands.logout.text'),
            'reply_markup' => InlineKeyboard::create($buttons),
        ];
    }

    public function onUpdateState(): void
    {
        //NOTE: если не существует - создаем пользователя авторизации
        /** @psalm-suppress DocblockTypeContradiction */
        if (!$this->task->User && $this->payload->user) {

            $repository = new UserRepository();

            $this->task->User =
                $repository->findOneById($this->payload->user)
                ?? $repository->makeUser(
                    id: $this->payload->user,
                    username: $this->update->getMessage()?->getFrom()?->getUsername(),
                );
            $this->task->saveOrFail();
        }
    }
}
