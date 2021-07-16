<?php

declare(strict_types=1);

use Yokai\Batch\BatchStatus;
use Yokai\Batch\Failure;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobExecutionLogs;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Summary;
use Yokai\Batch\Warning;

$hash = ['null' => null, 'string' => 'foo', 'array' => [], 'bool' => true, 'int' => 1, 'float' => 0.999];

$warning = new Warning('Please pay attention', $hash);

$failure = new Failure(
    'InvalidArgumentException',
    'An error occurred',
    666,
    $hash,
    "A stack trace\nSomething that indicates\nWhere the error occurred\nIn application code"
);

$jobExecution = JobExecution::createRoot(
    '123456789',
    'export',
    new BatchStatus(BatchStatus::FAILED),
    new JobParameters($hash),
    new Summary($hash),
    new JobExecutionLogs(
        <<<LOG
2020 [DEBUG]: Begin export
2020 [INFO]: Exported one row
2020 [WARNING]: Invalid row

LOG
    )
);
$jobExecution->setStartTime(\DateTimeImmutable::createFromFormat(DATE_ISO8601, '2018-01-01T00:00:01+0200'));
$jobExecution->setEndTime(\DateTimeImmutable::createFromFormat(DATE_ISO8601, '2018-01-01T01:59:59+0200'));
$jobExecution->addFailure($failure, false);
$jobExecution->addWarning($warning, false);
$jobExecution->addChildExecution(
    $prepareChildExecution = JobExecution::createChild(
        $jobExecution,
        'prepare',
        new BatchStatus(BatchStatus::COMPLETED),
        null,
        new Summary($hash)
    )
);
$prepareChildExecution->setStartTime(\DateTimeImmutable::createFromFormat(DATE_ISO8601, '2018-01-01T00:00:01+0200'));
$prepareChildExecution->setEndTime(\DateTimeImmutable::createFromFormat(DATE_ISO8601, '2018-01-01T00:59:59+0200'));
$prepareChildExecution->addWarning($warning, false);
$jobExecution->addChildExecution(
    $exportChildExecution = JobExecution::createChild(
        $jobExecution,
        'export',
        new BatchStatus(BatchStatus::FAILED),
        null,
        new Summary($hash)
    )
);
$exportChildExecution->setStartTime(\DateTimeImmutable::createFromFormat(DATE_ISO8601, '2018-01-01T01:00:00+0200'));
$exportChildExecution->setEndTime(\DateTimeImmutable::createFromFormat(DATE_ISO8601, '2018-01-01T01:59:59+0200'));
$exportChildExecution->addFailure($failure, false);

return $jobExecution;
