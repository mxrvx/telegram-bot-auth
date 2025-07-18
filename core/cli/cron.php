<?php

declare(strict_types=1);

use Go\Job;
use GO\Scheduler;

/** @var \modX $modx */
/** @psalm-suppress MissingFile */
require \dirname(__DIR__) . '/autoloader.php';
$scheduler = new Scheduler();

$scheduler->php(__DIR__ . '/clear-task.php', null, [], 'clear_task')
    ->hourly()
    ->inForeground()
    ->onlyOne();

$executed = $scheduler->run();
/** @var Job $job */
foreach ($executed as $job) {
    if ($output = $job->getOutput()) {
        if (\is_array($output)) {
            $output = \implode("\n", $output);
        }
        echo $output;
    }
}
