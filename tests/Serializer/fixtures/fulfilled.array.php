<?php

declare(strict_types=1);

$hash = ['null' => null, 'string' => 'foo', 'array' => [], 'bool' => true, 'int' => 1, 'float' => 0.999];
$failures = [
    [
        'class' => 'InvalidArgumentException',
        'message' => 'An error occurred',
        'code' => 666,
        'parameters' => $hash,
        'trace' => "A stack trace\nSomething that indicates\nWhere the error occurred\nIn application code",
    ],
];
$warnings = [
    [
        'message' => 'Please pay attention',
        'parameters' => $hash,
        'context' => [],
    ],
];
$logs = <<<LOG
2020 [DEBUG]: Begin export
2020 [INFO]: Exported one row
2020 [WARNING]: Invalid row

LOG;

return [
    'id' => '123456789',
    'jobName' => 'export',
    'status' => 6,
    'parameters' => $hash,
    'startTime' => '2018-01-01T00:00:01+0200',
    'endTime' => '2018-01-01T01:59:59+0200',
    'summary' => $hash,
    'failures' => $failures,
    'warnings' => $warnings,
    'childExecutions' => [
        [
            'id' => '123456789',
            'jobName' => 'prepare',
            'status' => 4,
            'parameters' => [],
            'startTime' => '2018-01-01T00:00:01+0200',
            'endTime' => '2018-01-01T00:59:59+0200',
            'summary' => $hash,
            'failures' => [],
            'warnings' => $warnings,
            'childExecutions' => [],
            'logs' => '',
        ],
        [
            'id' => '123456789',
            'jobName' => 'export',
            'status' => 6,
            'parameters' => [],
            'startTime' => '2018-01-01T01:00:00+0200',
            'endTime' => '2018-01-01T01:59:59+0200',
            'summary' => $hash,
            'failures' => $failures,
            'warnings' => [],
            'childExecutions' => [],
            'logs' => '',
        ],
    ],
    'logs' => $logs,
];
