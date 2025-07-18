<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Controllers\Mgr;

use MXRVX\Telegram\Bot\Auth\App;
use MXRVX\Telegram\Bot\Auth\Controllers\Controller;
use Psr\Http\Message\ResponseInterface;

class Version extends Controller
{
    public function get(): ResponseInterface
    {
        $results = [
            'current' => 'undefined',
            'available' => '',
        ];

        $pm = \MXRVX\Autoloader\App::packageManager();
        if ($package = $pm->getPackage(App::NAMESPACE)) {
            $results['current'] = $package->version;
            $results['available'] = \implode(' / ', $package->getAvailableVersions());
        }

        return $this->success($results);
    }
}
