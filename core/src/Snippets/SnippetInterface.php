<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Snippets;

use MXRVX\Schema\System\Settings;

interface SnippetInterface
{
    /**
     * @return array<array-key, mixed>
     */
    public function getAppProperties(): array;

    /**
     * @return array<array-key, mixed>
     */
    public function getScriptProperties(): array;

    /**
     * @return array<array-key, mixed>
     */
    public function getDefaultProperties(): array;

    /**
     * @return array<array-key, mixed>
     */
    public function getConfigProperties(bool $withNamespace = false): array;

    /**
     * @return array<Settings\Setting>
     */
    public function getDefaultSettings(): array;

    /**
     * @return array<Settings\Setting>
     */
    public function getConfigSettings(): array;

    /**
     * @return array<array-key, mixed>
     */
    public function getPls(): array;

    public function getOutput(array $pls = []): mixed;

    public function __invoke(): mixed;
}
