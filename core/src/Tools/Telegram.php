<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Tools;

use Longman\TelegramBot\Entities\Update as TelegramUpdate;
use Longman\TelegramBot\Entities\User as TelegramUser;
use MXRVX\Telegram\Bot\Auth\Telegram\Entities\Payload;

class Telegram
{
    public static function getUserFromUpdate(TelegramUpdate $update): ?TelegramUser
    {
        $message = $update->getMessage();
        $callback = $update->getCallbackQuery();
        $user = $message && $message->getFrom() ? $message->getFrom() : ($callback && $callback->getFrom() ? $callback->getFrom() : null);

        return $user instanceof TelegramUser ? $user : null;
    }

    public static function getInputFromUpdate(TelegramUpdate $update): string
    {
        $input = null;
        if ($callback = $update->getCallbackQuery()) {
            $input = $callback->getData();
        } elseif ($message = $update->getMessage()) {
            $input = $message->getText();
        }

        return Caster::string($input);
    }

    public static function getUpdatePayload(TelegramUpdate $update): Payload
    {
        $uuid = $command = $action = null;

        $input = self::getInputFromUpdate($update);
        if (!empty($input)) {
            if (\str_contains($input, Payload::CALLBACK_SEPARATOR)) {
                [$command, $uuid, $action] = \array_pad(\explode(Payload::CALLBACK_SEPARATOR, $input, 3), 3, null);
            } else {
                [, $uuid] = \array_pad(\explode(' ', $input, 2), 2, null);
            }
        }

        return Payload::make(
            user: self::getUserFromUpdate($update)?->getId(),
            uuid: $uuid,
            command: $command,
            action: $action,
        );
    }

    public static function maskNthChar(string $text, string $replacement = '*', int $n = 2): string
    {
        if (\mb_strlen($replacement) !== 1) {
            $replacement = '*';
        }

        $n = \max(2, $n);
        $chars = \preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $result = \array_map(static function ($char, $index) use ($replacement, $n) {
            return (($index + 1) % $n === 0) ? $replacement : $char;
        }, $chars, \array_keys($chars));

        return \implode('', $result);
    }

    public static function maskEmail(string $email, string $replacement = '*', int $n = 2): string
    {
        $atPos = \mb_strpos($email, '@');
        if ($atPos === false) {
            $atPos = \mb_strlen($email);
        }

        $localPart = \mb_substr($email, 0, $atPos);
        $domainPart = \mb_substr($email, $atPos);

        return self::maskNthChar($localPart, $replacement, $n) . $domainPart;
    }

    public static function generateRandomCode(int $length = 4): string
    {
        $length = \max(4, \min(9, $length));

        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= \random_int(0, 9);
        }

        return $code;
    }
}
