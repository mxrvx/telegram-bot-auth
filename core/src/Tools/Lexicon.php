<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Tools;

use MXRVX\Telegram\Bot\Auth\App;

class Lexicon
{
    protected static ?\modX $modx = null;
    protected static ?\modLexicon $lexicon = null;

    /**
     * @param array<array-key, array<array-key,mixed>|mixed> $arr
     * @return array<string, string>
     */
    public static function make(array $arr, string $prefix = '', string $delimiter = '.'): array
    {
        /** @var array<string, string> $out */
        $out = [];

        /** @var mixed $v */
        foreach ($arr as $k => $v) {
            $key = Caster::string(empty($prefix) ? $k : $prefix . $delimiter . $k);
            if (\is_array($v)) {
                $out += self::make($v, $key, $delimiter);
            } elseif (\is_scalar($v)) {
                $out[$key] = Caster::string($v, 0);
            }
        }

        return $out;
    }

    /**
     * @param array<array-key, array<array-key,string|int|float|bool>|string|int|float|bool> $placeholders
     */
    public static function item(string $key, array $placeholders = []): string
    {
        $item = $key;

        $lexicon = self::lexicon();
        if ($lexicon) {
            $lexicon->load(App::NAMESPACE . ':default');

            if (\str_starts_with($key, ':')) {
                $key = App::NAMESPACE . '.' . \mb_substr($key, 1);
            }

            if ($lexicon->exists($key)) {

                $placeholders = \array_filter($placeholders, static function ($value) {
                    return \is_scalar($value);
                });

                $item = $lexicon->process($key, $placeholders);

                if ($placeholders) {
                    $placeholders = self::make($placeholders);
                    foreach ($placeholders as $k => $v) {
                        $item = \str_replace(\sprintf('{%s}', $k), $v, $item);
                    }
                }
            } else {
                $item = $key;
            }
        }

        return $item;
    }

    public static function items(string $locale = 'en', array|string $prefix = []): array
    {
        $items = [];

        $lexicon = self::lexicon();
        if ($lexicon) {
            $lexicon->load($locale . ':' . App::NAMESPACE . ':default');


            if (!empty($prefix)) {
                if (\is_string($prefix)) {
                    $prefixes = [$prefix];
                } else {
                    $prefixes = \array_map(static function ($key) {
                        return (string) $key;
                    }, $prefix);
                }

                foreach ($prefixes as $prefix) {
                    $items += $lexicon->fetch(App::NAMESPACE . '.' . $prefix);
                }

            } else {
                $items = $lexicon->fetch(App::NAMESPACE);
            }

            $keys = \array_map(static function ($key) {
                return (string) \str_replace(App::NAMESPACE . '.', '', (string) $key);
            }, \array_keys($items));

            $items = \array_combine($keys, \array_values($items));
        }

        return $items;
    }

    protected static function lexicon(): ?\modLexicon
    {
        if (self::$lexicon === null) {
            /** @psalm-suppress DeprecatedMethod */
            $lexicon = self::modx()->getService('lexicon', 'modLexicon');
            if ($lexicon instanceof \modLexicon) {
                self::$lexicon = $lexicon;
            }
        }

        return self::$lexicon;
    }

    protected static function modx(): \modX
    {
        return self::$modx ??= \MXRVX\Autoloader\App::modx();
    }
}
