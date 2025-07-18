<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Snippets;

use MXRVX\Schema\System\Settings;
use MXRVX\Telegram\Bot\Auth\App;
use MXRVX\Telegram\Bot\Auth\AssetsManager;
use MXRVX\Telegram\Bot\Auth\Entities\Task;
use MXRVX\Telegram\Bot\Auth\Tools\Caster;

class AuthSnippet extends AbstractSnippet
{
    protected Task $task;

    public function __construct(protected \modX &$modx, protected array $scriptProperties = [])
    {
        $this->initialize();
        parent::__construct($modx, $scriptProperties);
    }

    public function getDefaultSettings(): array
    {
        /** @psalm-suppress DocblockTypeContradiction */
        return \array_merge(parent::getDefaultSettings(), [
            Settings\Setting::define(
                key: 'login_resource_id',
                value: $this->modx->resource?->get('id') ?? 0,
                xtype: 'textfield',
                area: 'config',
                typecast: Settings\TypeCaster::INTEGER,
            ),
            Settings\Setting::define(
                key: 'logout_resource_id',
                value: $this->modx->resource?->get('id') ?? 0,
                xtype: 'textfield',
                area: 'config',
                typecast: Settings\TypeCaster::INTEGER,
            ),
            Settings\Setting::define(
                key: 'context',
                value: $this->modx->context->get('key') ?? 'web',
                xtype: 'textfield',
                area: 'config',
                typecast: Settings\TypeCaster::STRING,
            ),
        ]);
    }

    public function getPls(): array
    {
        return parent::getPls() + ['task' => $this->task->toArray()];
    }

    public function __invoke(): mixed
    {
        $this->task->config = $this->getConfigProperties();
        $this->task->saveOrFail();

        $config = [
            'api_url' => App::API_URL,
            'namespace' => App::getNamespaceCamelCase(),
            'context' => $this->getConfigValue('context'),
        ];

        $this->modx->regClientHTMLBlock(\sprintf('<script>window["%s"]=%s;</script>', App::NAMESPACE, \json_encode($config, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE)));

        $withoutCss = (bool) $this->getConfigValue('without_css');
        AssetsManager::registerFrontendAssets($this->modx, $withoutCss);

        return $this->getOutput($this->getPls());
    }

    protected function initialize(): void
    {
        $sessionId = Caster::string(\session_id() ?: '');
        $this->task = $this->findOrMake(Task::class, $sessionId, [
            'session_id' => $sessionId,
            'uuid' => Caster::uuid($sessionId, true),
        ]);
    }
}
