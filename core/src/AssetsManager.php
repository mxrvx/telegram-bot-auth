<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth;

/**
 * @psalm-type ManifestItemStructure = array{
 * file: string,
 * name: string,
 * src: string,
 * isEntry: bool,
 *     css: array<string>,
 * }
 *
 * @psalm-type ManifestStructure = array<string, ManifestItemStructure>
 */
class AssetsManager
{
    //    public static function registerAssets(\modX|\modExtraManagerController &$instance, bool $noCss = false): void
    //    {
    //        if ($instance instanceof \modX) {
    //            self::registerFrontendAssets($instance, $noCss);
    //        }
    //        if ($instance instanceof \modExtraManagerController) {
    //            self::registerBackendAssets($instance, $noCss);
    //        }
    //    }

    public static function registerFrontendAssets(\modX &$modx, bool $withoutCss = false): void
    {
        $assets = self::getAssetsFromManifest('web');

        if ($assets) {
            //@NOTE: Production mode
            foreach ($assets as $file) {
                if (\str_ends_with($file, '.js')) {
                    $modx->regClientHTMLBlock('<script type="module" src="' . $file . '"></script>');
                } elseif (!$withoutCss) {
                    $modx->regClientCss($file);
                }
            }
        } else {
            //@NOTE: Development mode
            $port = \getenv('NODE_DEV_PORT') ?: '9090';
            $connection = @\fsockopen('node', (int) $port);
            if (@\is_resource($connection)) {
                $server = \explode(':', MODX_HTTP_HOST);
                $baseUrl = MODX_ASSETS_URL . 'components/' . App::NAMESPACE . '/';
                $vite = MODX_URL_SCHEME . $server[0] . ':' . $port . $baseUrl;
                $modx->regClientHTMLBlock('<script type="module" src="' . $vite . '@vite/client"></script>');
                $modx->regClientHTMLBlock('<script type="module" src="' . $vite . 'src/web.ts"></script>');
            }
        }
    }

    public static function registerBackendAssets(\modExtraManagerController &$controller, bool $withoutCss = false): void
    {
        $assets = self::getAssetsFromManifest('mgr');

        if ($assets) {
            //@NOTE: Production mode
            foreach ($assets as $file) {
                if (\str_ends_with($file, '.js')) {
                    $controller->addHtml('<script type="module" src="' . $file . '"></script>');
                } elseif (!$withoutCss) {
                    $controller->addCss($file);
                }
            }
        } else {
            //@NOTE: Development mode
            $port = \getenv('NODE_DEV_PORT') ?: '9090';
            $connection = @\fsockopen('node', (int) $port);
            if (@\is_resource($connection)) {
                $server = \explode(':', MODX_HTTP_HOST);
                $baseUrl = MODX_ASSETS_URL . 'components/' . App::NAMESPACE . '/';
                $vite = MODX_URL_SCHEME . $server[0] . ':' . $port . $baseUrl;
                $controller->addHtml('<script type="module" src="' . $vite . '@vite/client"></script>');
                $controller->addHtml('<script type="module" src="' . $vite . 'src/mgr.ts"></script>');
            }
        }
    }

    /**
     * @return array<string>
     */
    protected static function getAssetsFromManifest(string $context): array
    {
        $baseUrl = MODX_ASSETS_URL . 'components/' . App::NAMESPACE . '/src/' . $context . '/';
        $manifest = MODX_ASSETS_PATH . 'components/' . App::NAMESPACE . '/src/' . $context . '/manifest.json';

        $assets = [];
        if (\file_exists($manifest) && \is_string($content = @\file_get_contents($manifest))) {

            /** @var ManifestStructure $files */
            $files = \json_decode($content, true);
            if (\json_last_error() !== JSON_ERROR_NONE) {
                $files = [];
            }

            foreach ($files as $name => $file) {
                if (!empty($file['css'])) {
                    foreach ($file['css'] as $css) {
                        $assets[] = $baseUrl . $css;
                    }
                }

                if (\str_contains($name, '.ts')) {
                    $assets[] = $baseUrl . $file['file'];
                }

            }
        }

        return $assets;
    }
}
