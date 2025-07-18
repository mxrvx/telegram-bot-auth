<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Snippets;

use DI\Container;
use MXRVX\ORM\AR\AR;
use MXRVX\Schema\System\Settings;
use MXRVX\Schema\System\Settings\SchemaConfig;
use MXRVX\Schema\System\Settings\SchemaConfigInterface;
use MXRVX\Telegram\Bot\Auth\App;

abstract class AbstractSnippet implements SnippetInterface
{
    protected Container $container;
    protected App $app;

    /** @var array<array-key, mixed> */
    protected array $properties = [];

    protected SchemaConfigInterface $config;

    /** @var \pdoTools|null */
    /** @psalm-suppress UndefinedClass */
    protected mixed $pdoTools = null;

    /**
     * @param array<array-key, mixed> $scriptProperties
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(protected \modX &$modx, protected array $scriptProperties = [])
    {
        $this->container = \MXRVX\Autoloader\App::container();

        /** @var App $this->app */
        $this->app = $this->container->get(App::class);
        $this->properties = $this->initProperties();
        $this->config = $this->initConfig();

        /** @psalm-suppress UndefinedClass */
        /** @psalm-suppress MixedAssignment */
        $this->pdoTools = \class_exists(\pdoTools::class) ? new \pdoTools($modx) : null;
    }

    public function getChunk(string $tpl, array $properties = []): string
    {
        if (\is_object($this->pdoTools) && \method_exists($this->pdoTools, 'getChunk')) {
            $output = (string) $this->pdoTools->getChunk($tpl, $properties);
        } else {
            $output = $this->modx->getChunk($tpl, $properties);
        }

        return $output;
    }

    public function getOutput(array $pls = []): mixed
    {
        $tpl = (string) $this->getConfigValue('tpl');
        $return = (string) $this->getConfigValue('return');
        $output = match ($return) {
            'data' => $pls,
            'json' => \json_encode($pls, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            default => $this->getChunk($tpl, $pls),
        };

        return $output;
    }

    public function getDefaultProperties(): array
    {
        /** @var array<array-key, mixed> $properties */
        $properties = [];

        foreach ($this->getDefaultSettings() as $setting) {
            if ($setting instanceof Settings\Setting) {
                /** @var mixed $value */
                $value = $setting->getValue();
                if ($value !== null) {
                    $key = $setting->getKey(App::NAMESPACE);
                    /** @psalm-suppress MixedAssignment */
                    $properties[$key] = $value;
                }
            }
        }

        return $properties;
    }

    public function getAppProperties(): array
    {
        /** @var array<array-key, mixed> $properties */
        $properties = [];

        foreach ($this->app->config->getSchema()->getSettings() as $setting) {
            /** @var mixed $value */
            $value = $setting->getValue();
            if ($value !== null) {
                $key = $setting->getKey(App::NAMESPACE);
                /** @psalm-suppress MixedAssignment */
                $properties[$key] = $value;
            }
        }

        return $properties;
    }

    public function getScriptProperties(): array
    {
        /** @var array<array-key, mixed> $properties */
        $properties = [];

        /** @var mixed $value */
        foreach ($this->scriptProperties as $key => $value) {
            if ($value !== null) {
                $key = \sprintf('%s.%s', App::NAMESPACE, \trim((string) $key));
                /** @psalm-suppress MixedAssignment */
                $properties[$key] = $value;
            }
        }
        return $properties;
    }

    public function initProperties(): array
    {
        return \array_replace($this->getAppProperties(), $this->getDefaultProperties(), $this->getScriptProperties());
    }

    public function getConfigValue(string $key): mixed
    {
        return $this->config->getSettingValue($key);
    }

    public function getDefaultSettings(): array
    {
        return [
            Settings\Setting::define(
                key: 'return',
                value: '',
                xtype: 'textfield',
                typecast: Settings\TypeCaster::STRING,
            ),
            Settings\Setting::define(
                key: 'tpl',
                value: '',
                xtype: 'textfield',
                typecast: Settings\TypeCaster::STRING,
            ),
            Settings\Setting::define(
                key: 'pls',
                value: '',
                xtype: 'textfield',
                typecast: [Settings\TypeCaster::STRING, Settings\TypeCaster::ARRAY],
            ),
            Settings\Setting::define(
                key: 'without_css',
                value: false,
                xtype: 'combo-boolean',
                typecast: Settings\TypeCaster::BOOLEAN,
            ),
        ];
    }

    public function getConfigSettings(): array
    {
        return $this->config->getSchema()->getSettingsByArea('config');
    }

    public function getConfigProperties(bool $withNamespace = false): array
    {
        /** @var array<array-key, mixed> $properties */
        $properties = [];

        foreach ($this->getConfigSettings() as $setting) {
            /** @var mixed $value */
            $value = $setting->getValue();
            if ($value !== null) {
                $key = $setting->getKey($withNamespace ? App::NAMESPACE : null);
                /** @psalm-suppress MixedAssignment */
                $properties[$key] = $value;
            }
        }

        return $properties;
    }

    /**
     * @throws \Exception
     */
    public function initConfig(): SchemaConfig
    {
        $schema = $this->app->config->getSchema();

        $settings = \array_filter(
            $this->getDefaultSettings(),
            static fn($setting): bool => $setting instanceof Settings\Setting,
        );
        foreach ($settings as $setting) {
            $schema->withSetting($setting);
        }

        return SchemaConfig::define($schema)->withConfig($this->properties);
    }

    public function getPls(): array
    {
        /** @var array<array-key, mixed>|null $pls */
        $pls = $this->getConfigValue('pls') ?? [];

        return \is_array($pls) ? $pls : [];
    }

    abstract public function __invoke(): mixed;

    /**
     * @template T of AR
     * @param class-string<T> $className
     * @param mixed $id
     * @param array<non-empty-string, mixed> $defaults
     * @return T
     */
    protected function findOrMake(string $className, int|string $id, array $defaults): AR
    {
        if ($id) {
            $entity = $className::findByPK($id);
            if ($entity !== null) {
                return $entity;
            }
        }
        /** @var T $entity */
        $entity = $className::make($defaults);
        return $entity;
    }
}
