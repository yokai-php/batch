# Job execution storage

## What is a job execution storage ?

Whenever a job is launched, whether is starts immediately or not, 
an execution is stored for it.

The execution are stored to allow you to keep an eye on what is happening.

This persistence is on the responsibility of the job execution storage.

## How do I store my Job Execution ?

You should never be forced to store `JobExecution` yourself.

This is [JobLauncher](job-launcher.md)'s job to store it whenever it is required 
(usually at the beginning and the end of the job execution).

## How can I retrieve a Job Execution afterwards ?

Every storage implements [JobExecutionStorageInterface](../../src/Storage/JobExecutionStorageInterface.php) 
that has a method called `retrieve`.
Use this method to retrieve one execution using job name and execution id.

Depending on which storage you decided to use, you may also be able to:
- list of all executions for particular job, if your storage implements
  [ListableJobExecutionStorageInterface](../../src/Storage/ListableJobExecutionStorageInterface.php):
- filter list of executions matching criteria you provided, if your storage implements
  [QueryableJobExecutionStorageInterface](../../src/Storage/QueryableJobExecutionStorageInterface.php):

> **Note:** Sometimes the storage may implement these interfaces, but
> due to the way executions are stored, it might not be recommended heavily rely on these extra methods.

## What types of storages exists ?

**Built-in storages:**
- [NullJobExecutionStorage](../../src/Storage/NullJobExecutionStorage.php):
  do not persist any job execution.
- [FilesystemJobExecutionStorage](../../src/Storage/FilesystemJobExecutionStorage.php):
  store job executions to a file on local filesystem.

**Storages from bridges:**
- [DoctrineDBALJobExecutionStorage (`doctrine/dbal`)](https://github.com/yokai-php/batch-doctrine-dbal/blob/0.x/src/DoctrineDBALJobExecutionStorage.php):
  store job executions to a relational database.

**Storages for testing purpose:**
- [InMemoryJobExecutionStorage](../../src/Test/Storage/InMemoryJobExecutionStorage.php):
  store executions in a private var that can be accessed afterwards in your tests.

## On the same subject

- [How do I create my own storage ?](../recipes/custom-job-execution-storage.md)
