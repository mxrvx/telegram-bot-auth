<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth;

/**
 * @psalm-type ManifestItemStructure = array{
 * file: string,
 * name: string,
 * src: string,
 * isEntry: bool,
 * css: array<string>
 * }
 *
 * @psalm-type ManifestStructure = array<string, ManifestItemStructure>
 *
 * @psalm-type AssetsStructure = array{
 * module: array<string>,
 * nomodule: array<string>,
 * css: array<string>
 * }
 */
class AssetsManager
{
    protected const DEV_DEFAULT_PORT = 9090;

    public static function registerFrontendAssets(\modX $modx, bool $withoutCss = false): void
    {
        /** @psalm-suppress MissingClosureReturnType */
        self::registerAssets(
            $modx,
            'web',
            $withoutCss,
            static fn(string $file, bool $isModule) => $modx->regClientHTMLBlock(\sprintf('<script src="%s" %s></script>', $file, $isModule ? 'type="module"' : 'nomodule')),
            static fn(string $file) => $modx->regClientCss($file),
        );
    }

    public static function registerBackendAssets(\modExtraManagerController $controller, bool $withoutCss = false): void
    {
        /** @psalm-suppress MissingClosureReturnType */
        self::registerAssets(
            $controller,
            'mgr',
            $withoutCss,
            static fn(string $file, bool $isModule) => $controller->addHtml(\sprintf('<script src="%s" %s></script>', $file, $isModule ? 'type="module"' : 'nomodule')),
            static fn(string $file) => $controller->addCss($file),
        );
    }

    /**
     * @param callable(string, bool): void $jsRegister
     * @param callable(string): void $cssRegister
     */
    protected static function registerAssets(
        \modX|\modExtraManagerController $instance,
        string                           $context,
        bool                             $withoutCss,
        callable                         $jsRegister,
        callable                         $cssRegister,
    ): void {
        $assets = self::getAssetsFromManifest($context);

        if (self::isEmpty($assets)) {
            self::registerDevelopmentAssets($instance, $context);
        } else {
            foreach ($assets['nomodule'] as $file) {
                $jsRegister($file, false);
            }
            foreach ($assets['module'] as $file) {
                $jsRegister($file, true);
            }
            if (!$withoutCss) {
                foreach ($assets['css'] as $file) {
                    $cssRegister($file);
                }
            }

        }
    }

    protected static function registerDevelopmentAssets(\modX|\modExtraManagerController $instance, string $context): void
    {
        $port = (int) (\getenv('NODE_DEV_PORT') ?: self::DEV_DEFAULT_PORT);
        $connection = @\fsockopen('node', $port, $errno, $errstr, 0.1);

        if (\is_resource($connection)) {
            \fclose($connection);

            $host = self::getHostFromModxHttpHost();

            $baseAssetPath = MODX_ASSETS_URL . 'components/' . App::NAMESPACE . '/';
            $viteUrl = MODX_URL_SCHEME . $host . ':' . $port . '/' . \ltrim($baseAssetPath, '/');

            $clientScript = \sprintf('<script type="module" src="%s@vite/client"></script>', $viteUrl);
            $mainScript = \sprintf('<script type="module" src="%ssrc/%s.ts"></script>', $viteUrl, $context);

            if ($instance instanceof \modX) {
                $instance->regClientHTMLBlock($clientScript);
                $instance->regClientHTMLBlock($mainScript);
            } else {
                $instance->addHtml($clientScript);
                $instance->addHtml($mainScript);
            }
        }
    }

    protected static function getHostFromModxHttpHost(): string
    {
        /** @psalm-suppress TypeDoesNotContainType */
        if (!\defined('MODX_HTTP_HOST') || empty(MODX_HTTP_HOST)) {
            return 'localhost';
        }

        /** @var string $host */
        $host = MODX_HTTP_HOST;
        if (\str_starts_with($host, 'http://') || \str_starts_with($host, 'https://')) {
            $parsedHost = \parse_url($host, PHP_URL_HOST);
            if (!empty($parsedHost)) {
                return $parsedHost;
            }
        }

        $hostParts = \explode(':', $host, 2);
        if (!empty($hostParts[0])) {
            return $hostParts[0];
        }

        return 'localhost';
    }

    /**
     * @return AssetsStructure
     */
    protected static function getAssetsFromManifest(string $context): array
    {
        $assets = [
            'module' => [],
            'nomodule' => [],
            'css' => [],
        ];

        $baseUrl = MODX_ASSETS_URL . 'components/' . App::NAMESPACE . '/src/' . $context . '/';
        $manifestPath = MODX_ASSETS_PATH . 'components/' . App::NAMESPACE . '/src/' . $context . '/manifest.json';

        if (!\file_exists($manifestPath) || !\is_readable($manifestPath)) {
            return $assets;
        }

        $content = \file_get_contents($manifestPath);
        if ($content === false) {
            return $assets;
        }

        /** @var ManifestStructure $manifest */
        $manifest = \json_decode($content, true);
        if (\json_last_error() !== JSON_ERROR_NONE) {
            return $assets;
        }

        foreach ($manifest as $name => $entry) {
            $isModule = \str_contains($name, 'legacy') ? false : true;

            /** @psalm-suppress RedundantCondition */
            if (!empty($entry['css']) && \is_array($entry['css'])) {
                foreach ($entry['css'] as $cssFile) {
                    $assets['css'][] = $baseUrl . $cssFile;
                }
            }

            if (!empty($entry['file'])) {
                foreach (['.js', '.ts'] as $ext) {
                    if (\str_ends_with($entry['file'], $ext)) {
                        $assets[$isModule ? 'module' : 'nomodule'][] = $baseUrl . $entry['file'];
                        break;
                    }
                }
            }
        }

        return $assets;
    }

    /**
     * @param AssetsStructure $assets
     *
     * @psalm-assert-if-true AssetsStructure $assets is empty
     */
    protected static function isEmpty(array $assets): bool
    {
        return \count(\array_filter($assets, static fn($a) => !empty($a))) === 0;
    }
}
