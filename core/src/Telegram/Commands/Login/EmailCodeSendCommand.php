<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Commands\Login;

use MXRVX\Telegram\Bot\Auth\Services\EmailSender;
use MXRVX\Telegram\Bot\Auth\Telegram\Commands\AbstractChainCommand;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;
use MXRVX\Telegram\Bot\Auth\Tools\Telegram;

/** @psalm-suppress PropertyNotSetInConstructor */
class EmailCodeSendCommand extends AbstractChainCommand
{
    protected $name = 'login::email::code::send';
    protected $description = 'Вход: отправка кода на почту пользователя';
    protected $usage = '/login::email::code::send';

    public function shouldRun(): bool
    {
        return !$this->task->getIsSuccess();
    }

    public function getExecuteData(array $data = []): ?array
    {
        $email = $this->user->getEmail();
        $code = Telegram::generateRandomCode();

        $this->task
            ->setCommandData(
                $this->getCommandAlias(),
                [
                    'email' => $email,
                    'code' => $code,
                ],
            );

        $this->task->saveOrFail();

        $pls = ['code' => $code, 'email' => $email];
        $subject = Lexicon::item(':commands.login::email::code::send.email.subject', $pls);
        $body = Lexicon::item(':commands.login::email::code::send.email.body', $pls);

        if ((new EmailSender($this->modx))->send($email, $subject, $body)) {
            return $this->executeCommandInstance(EmailCodeQueryCommand::class);
        }

        return null;
    }
}
