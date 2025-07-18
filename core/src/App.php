<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth;

use MXRVX\Schema\System\Settings\SchemaConfigInterface;
use MXRVX\Telegram\Bot\App as BotApp;
use MXRVX\Telegram\Bot\Auth\Telegram\Callbacks;
use MXRVX\Telegram\Bot\Auth\Telegram\Listeners;

class App
{
    public const NAMESPACE = 'mxrvx-telegram-bot-auth';
    public const ASSETS_URL = MODX_ASSETS_URL . 'components/' . self::NAMESPACE . '/';
    public const API_URL = self::ASSETS_URL . 'api/connector/';
    public const API_ACTION_URL = self::ASSETS_URL . 'api/action/';
    public const TELEGRAM_URL = 'https://t.me/';

    public SchemaConfigInterface $config;

    public function __construct(public \modX $modx, public BotApp $botApp)
    {
        $this->config = Config::make($modx->config);
    }

    public static function injectDependencies(\modX $modx): void
    {
        self::injectTelegram($modx);
    }

    public static function injectTelegram(\modX $modx): void
    {
        BotApp::addListenerClass(Listeners\Listener::class);
        BotApp::addCallbackClass(Callbacks\CommandCallback::class);
    }

    public static function getNamespaceCamelCase(): string
    {
        return \lcfirst(\str_replace(' ', '', \ucwords(\str_replace('-', ' ', App::NAMESPACE))));
    }

    public function getTelegramStartUrl(string $startQuery): string
    {
        return \sprintf('%s%s?start=%s', self::TELEGRAM_URL, $this->botApp->getBotUsername(), $startQuery);
    }

    public function log(string $message): void
    {
        if (\method_exists($this->modx, 'log')) {
            $this->modx->log(\xPDO::LOG_LEVEL_ERROR, $message);
        }
    }
}
