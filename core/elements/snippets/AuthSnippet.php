<?php

declare(strict_types=1);

/**
 * AuthSnippet
 * @var \modX $modx
 * @var array $scriptProperties
 */

use MXRVX\Telegram\Bot\Auth\Snippets\AuthSnippet;

try {
    return (new AuthSnippet($modx, $scriptProperties))();
} catch (\Throwable $e) {
    $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf("\nError: %s\nFile: %s \nLine: %s", $e->getMessage(), $e->getFile(), $e->getLine()));
}

return \sprintf('Class `%s` not found', AuthSnippet::class);
