<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Console\Command;

use MXRVX\ORM\MODX\Entities\Category;
use MXRVX\ORM\MODX\Entities\MediaSource;
use MXRVX\ORM\MODX\Entities\Namespaces;
use MXRVX\ORM\MODX\Entities\Snippet;
use MXRVX\ORM\MODX\Entities\SystemSetting;
use MXRVX\Telegram\Bot\Auth\App;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    protected static $defaultName = 'install';
    protected static $defaultDescription = 'Install "' . App::NAMESPACE . '" extra for MODX';

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = $this->app;

        $srcPath = MODX_CORE_PATH . 'vendor/' . (string) \preg_replace('/-/', '/', App::NAMESPACE, 1);
        $corePath = MODX_CORE_PATH . 'components/' . App::NAMESPACE;
        if (!\is_dir($corePath)) {
            \symlink($srcPath . '/core', $corePath);
            $output->writeln('<info>Created symlink for `core`</info>');
        }

        $assetsPath = MODX_ASSETS_PATH . 'components/' . App::NAMESPACE;
        if (!\is_dir($assetsPath)) {
            \symlink($srcPath . '/assets', $assetsPath);
            $output->writeln('<info>Created symlink for `assets`</info>');
        }

        if (!Namespaces::findByPK(App::NAMESPACE)) {
            $namespace = Namespaces::make([
                'name' => App::NAMESPACE,
                'path' => '{core_path}components/' . App::NAMESPACE . '/',
                'assets_path' => '',
            ]);
            $namespace->saveOrFail();
            $output->writeln(\sprintf('<info>Created namespace `%s`</info>', $namespace->name));
        }

        if (!$category = Category::findOne(['category' => App::NAMESPACE])) {
            $category = Category::make([
                'category' => App::NAMESPACE,
                'parent' => 0,
            ]);
            $category->saveOrFail();
            $output->writeln(\sprintf('<info>Created category `%s`</info>', $category->category));
        }

        if (!$mediaSource = MediaSource::findOne(['name' => 'Filesystem'])) {
            $mediaSource = MediaSource::make([
                'name' => 'Filesystem',
                'properties' => MediaSource::getDefaultProperties(),
            ]);
            $mediaSource->saveOrFail();
            $output->writeln(\sprintf('<info>Created media source `%s`</info>', $mediaSource->name));
        }


        $postfix = 'Snippet.php';
        $files = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($corePath . '/elements/snippets/'),
            ),
            \sprintf('/^.+%s$/', $postfix),
        );

        /** @var \SplFileInfo[] $files */
        foreach ($files as $file) {
            $snippetPath = $file->getRealPath();
            $snippetName = \sprintf('%s.%s', App::getNamespaceCamelCase(), \str_replace($postfix, '', $file->getFilename()));

            if (!$snippet = Snippet::findOne(['name' => $snippetName])) {
                $snippet = Snippet::make([
                    'name' => $snippetName,
                ]);
            }

            $snippet->Source = $mediaSource;
            $snippet->Category = $category;
            $snippet->static = true;
            $snippet->static_file = \str_replace(MODX_BASE_PATH, '', $snippetPath);

            $properties = [];
            $snippetPropertiesPath = \sprintf('%s/%s.Properties.php', $file->getPath(), $file->getBasename('.php'));
            if (\file_exists($snippetPropertiesPath)) {
                /** @var array{key: string, value: mixed}|mixed $properties */
                $properties = require $snippetPropertiesPath;
                $properties = \is_array($properties) ? $properties : [];
            }
            $snippet->properties = $properties;

            $action = empty($snippet->id) ? 'Create' : 'Update';

            $snippet->saveOrFail();
            $output->writeln(\sprintf('<info>%s snippet `%s`</info>', $action, $snippet->name));
        }


        /** @var array{key: string, value: mixed} $row */
        foreach ($app->config->getSettingsArray() as $row) {
            if (!SystemSetting::findByPK($row['key'])) {
                $setting = SystemSetting::make($row);
                $setting->saveOrFail();
                $output->writeln(\sprintf('<info>Created system setting `%s`</info>', $setting->key));
            }
        }

        $output->writeln('<info>Run Migrations</info>');

        $command = [
            'command' => 'migration:up',
            'params' => [
                '--namespace' => App::NAMESPACE,
            ],
        ];

        try {
            $returnCode = $this->runCommand(
                command: $command['command'],
                params: $command['params'],
                output: $output,
            );
        } catch (\Throwable $e) {
            $returnCode = Command::FAILURE;
            $output->writeln(\sprintf('<error>Exception occurred: %s</error>', $e->getMessage()));
        }

        if ($returnCode === Command::SUCCESS) {
            $output->writeln(\sprintf('<info>Command `%s` executed successfully</info>', $command['command']));
        } else {
            $output->writeln(\sprintf(
                '<error>Command `%s` failed with return code `%s`</error>',
                $command['command'],
                $returnCode,
            ));

            return $returnCode;
        }

        \MXRVX\Autoloader\App::cacheManager()->clearCache();
        $output->writeln('<info>Cleared MODX cache</info>');

        return self::SUCCESS;
    }
}
