<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Controllers\Web\Auth;

use MXRVX\Telegram\Bot\Auth\App;
use MXRVX\Telegram\Bot\Auth\Tools\Lexicon;
use Psr\Http\Message\ResponseInterface;

class Config extends Action
{
    public function getStore(): array
    {
        return [
            'task' => $this->task->toArray(['uuid']),
            'user' => $this->user->toArray(['id', 'username'])
                + ($this->user->Profile?->toArray(['fullname', 'email', 'phone', 'mobilephone', 'photo']) ?? []),
        ];
    }

    public function get(): ResponseInterface
    {
        /** @var App $app */
        $app = $this->container->get(App::class);

        $locale = (string) ($this->modx->context->getOption('cultureKey') ?: 'en');
        $config = [
            'locale' => $locale,
            'context' => $this->context->key,
            'lexicon' => Lexicon::items($locale, ['errors']),
            'ably_api_key' => (string) $app->config->getSetting('ably_public_api_key')?->getStringValue(),
            'store' => $this->getStore(),
        ];

        return $this->success($config);
    }

    public function post(): ResponseInterface
    {
        return $this->failure();
    }
}
