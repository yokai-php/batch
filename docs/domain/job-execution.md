# Job execution

## What is a Job execution ?

A [JobExecution](../../src/JobExecution.php) is the class that holds information about one execution of a [job](job.md).

## What kind of information does it hold ?

- `JobExecution::$jobName` : The Job name (job id)
- `JobExecution::$id` : The execution id
- `JobExecution::$parameters` : Some parameters with which job was executed
- `JobExecution::$status` : A status (pending, running, stopped, completed, abandoned, failed)
- `JobExecution::$startTime` : Start time
- `JobExecution::$endTime` : End time
- `JobExecution::$failures` : A list of failures (usually exceptions)
- `JobExecution::$warnings` : A list of warnings (usually skipped items)
- `JobExecution::$summary` : A summary (can contain any data you wish to store)
- `JobExecution::$logs` : Some logs
- `JobExecution::$childExecutions` : Some child execution

## On the same subject

- [How do I create a job execution ?](job-launcher.md)
- [How do I get a job execution afterwards ?](job-execution-storage.md)
