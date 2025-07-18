<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Services;

use Ably\AblyRest;
use Ably\Channel;
use MXRVX\Telegram\Bot\Auth\App;
use MXRVX\Telegram\Bot\Auth\Entities\Task;

class TaskEventSender
{
    protected const EVENT_NAME = 'task';

    protected AblyRest $client;

    public function __construct(protected Task $task)
    {
        /** @var \DI\Container $container */
        $container = \MXRVX\Autoloader\App::container();
        /** @var App $app */
        $app = $container->get(App::class);
        $this->client = new AblyRest((string) $app->config->getSetting('ably_private_api_key')?->getStringValue());
    }

    public function getChannelName(): string
    {
        return \sprintf('private:%s:%s', App::NAMESPACE, $this->task->uuid);
    }

    public function getChannel(): ?Channel
    {
        return $this->client->channel($this->getChannelName());
    }

    public function send(array $data = []): bool
    {
        if ($channel = $this->getChannel()) {
            $channel->publish(self::EVENT_NAME, \json_encode($this->getData($data), \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE));
            return true;
        }
        return false;
    }

    public function getData(array $add = []): array
    {
        $data = [Task::FIELD_UUID => $this->task->getUuid()];
        if ($this->task->getIsSuccess()) {
            $url = \sprintf('%s/%s/?%s', \trim(MODX_SITE_URL, '/'), \trim(App::API_ACTION_URL, '/'), \http_build_query($data));
            $data += ['redirect' => $url];
        }

        return \array_merge($data, $add);
    }
}
