<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Tools;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Validator\GenericValidator as UuidValidator;

class Caster
{
    protected static ?UuidValidator $uuidValidator;

    public static function int(mixed $value): int
    {
        return match (true) {
            $value === null => 0,
            \is_bool($value) => $value ? 1 : 0,
            \is_int($value) => $value,
            \is_float($value) => (int) $value,
            \is_string($value) => \is_numeric(\trim($value)) ? (int) \trim($value) : 0,
            default => 0,
        };
    }

    public static function intPositive(mixed $value): int
    {
        $value = self::int($value);
        return \max($value, 0);
    }

    public static function bool(mixed $value): bool
    {
        $value = match (true) {
            \is_bool($value) => $value,
            \is_int($value) => \in_array($value, [0, 1], true) ? $value === 1 : null,
            \is_string($value) => match (\trim(\strtolower($value))) {
                '1', 'true' => true,
                '0', 'false' => false,
                default => null,
            },

            default => null,
        };

        return $value ?? false;
    }

    public static function string(mixed $value, int $length = 191, string $encoding = 'UTF-8'): string
    {
        $value = match (true) {
            \is_string($value) => \trim($value),
            \is_int($value), \is_float($value), \is_bool($value) => (string) $value,
            default => null,
        };

        if (\is_string($value) && $length > 0) {
            $value = \mb_substr($value, 0, $length, $encoding);
        }

        return $value ?? '';
    }

    public static function uuid(mixed $value, bool $generate = false): string
    {
        $value = self::string($value, 0);
        if ($generate) {
            return Uuid::uuid5('6ba7b810-9dad-11d1-0001-00c04fd430c8', $value)->toString();
        }

        $validator = self::getUuidValidator();
        if (!$validator->validate($value)) {
            return '';
        }

        return $value;
    }

    public static function array(mixed $value): array
    {
        return match (true) {
            \is_array($value) => $value,
            $value === null => [],
            \is_object($value) => (array) $value,
            \is_string($value) && ($value[0] === '[' || $value[0] === '{') => (static function (string $str) {
                /** @var array|null $decoded */
                $decoded = \json_decode($str, true);
                return (\json_last_error() === JSON_ERROR_NONE && \is_array($decoded)) ? $decoded : [];
            })($value),
            default => [$value],
        };
    }

    public static function phone(mixed $value, int $length = 15): string
    {
        $value = self::string($value);

        $value = (string) \preg_replace('/[^0-9]/', '', $value);

        if ($length > 0) {
            $value = \mb_substr($value, 0, $length, 'UTF-8');
        }

        return $value;
    }

    public static function email(mixed $value, int $length = 100): string
    {
        $value = self::string($value);

        $value = \mb_strtolower(self::string($value), 'utf-8');
        if (!\preg_match('/^\S+@\S+[.]\S+$/', $value)) {
            $value = '';
        }

        if ($length > 0) {
            $value = \mb_substr($value, 0, $length, 'UTF-8');
        }

        return $value;
    }

    protected static function getUuidValidator(): UuidValidator
    {
        return self::$uuidValidator ??= new UuidValidator();
    }
}
