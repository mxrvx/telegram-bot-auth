<?php


declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Telegram\Entities;

use Longman\TelegramBot\Entities\InlineKeyboard as BaseInlineKeyboard;

class InlineKeyboard extends BaseInlineKeyboard
{
    public static function create(array $buttons = []): BaseInlineKeyboard
    {
        return new BaseInlineKeyboard(...self::prepareButtons($buttons));
    }

    //    public static function prepareKeyboard() {
    //
    //    }

    protected static function prepareButtons(array $buttons = [], int $maxPerRow = 3): array
    {
        $result = [];
        $count = \count($buttons);
        $i = 0;

        while ($i < $count) {
            $result[] = \array_slice($buttons, $i, $maxPerRow);
            $i += $maxPerRow;
        }

        return $result;
    }

    protected static function prepareButtons1(array $buttons = []): array
    {
        $array = [];

        $i = 0;
        $odd = true;
        $count = \count($buttons);
        while ($i <= $count) {
            $hasOne = !empty($buttons[$i]);
            $hasTwo = $hasOne && !empty($buttons[$i + 1]);

            if ($count >= 10) {
                if ($odd && $hasTwo && !empty($buttons[$i + 2])) {
                    $array[] = [$buttons[$i], $buttons[$i + 1], $buttons[$i + 2]];
                    $odd = false;
                    $i += 3;
                } elseif ($hasTwo) {
                    $array[] = [$buttons[$i], $buttons[$i + 1]];
                    $odd = true;
                    $i += 2;
                } elseif ($hasOne) {
                    $array[] = [$buttons[$i]];
                    ++$i;
                } else {
                    break;
                }
            } elseif ($hasTwo) {
                $array[] = [$buttons[$i], $buttons[$i + 1]];
                $i += 2;
            } elseif ($hasOne) {
                $array[] = [$buttons[$i]];
                ++$i;
            } else {
                break;
            }
        }

        return $array;
    }
}
