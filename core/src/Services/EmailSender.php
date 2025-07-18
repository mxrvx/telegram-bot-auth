<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Services;

class EmailSender
{
    public function __construct(protected \modX $modx) {}

    public function send(string $email, string $subject, string $body): bool
    {
        $this->modx->getParser()->processElementTags('', $body, true, false, '[[', ']]', [], 10);
        $this->modx->getParser()->processElementTags('', $body, true, true, '[[', ']]', [], 10);

        /** @psalm-suppress MissingFile */
        if (!\class_exists(\modPHPMailer::class) && \file_exists(MODX_CORE_PATH . 'model/modx/mail/modphpmailer.class.php')) {
            require_once MODX_CORE_PATH . 'model/modx/mail/modphpmailer.class.php';
        }

        $service = new \modPHPMailer($this->modx);
        $service->setHTML(true);
        $service->address('to', $email);
        $service->set(\modMail::MAIL_SUBJECT, $subject);
        $service->set(\modMail::MAIL_BODY, $body);
        $service->set(\modMail::MAIL_FROM, $this->modx->getOption('emailsender'));
        $service->set(\modMail::MAIL_FROM_NAME, $this->modx->getOption('site_name'));
        if (!$service->send()) {
            $this->modx->log(
                \modX::LOG_LEVEL_ERROR,
                'An error occurred while trying to send the email: ' . $service->mailer->ErrorInfo,
            );
            return false;
        }
        $service->reset();
        return true;
    }
}
