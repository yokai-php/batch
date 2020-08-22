# Vocabulary

Because when you start with any library 
it is important to understand what are the concepts introduced in it.

This is highly recommended that you read this entire page 
before starting to work with this library.


## Job

This is where you are going to work as a developer.

The job is a class, that implements the `Yokai\Batch\Job\JobInterface`.

It has only one method `execute`, that will perform the whole operation.
It takes an execution as single parameter, this objects hold contextual information.

[documentation](domain/job.md)


## Job Item

A special job which is responsible for batch processing logic.

[documentation](domain/job-item.md)


## Job Launcher

The main entry point of any job execution.

[documentation](domain/job-launcher.md)


## Job Execution

The representation of a certain execution of certain job.

[documentation](domain/job-execution.md)


## Job Execution Storage

The class responsible for job execution persistence.

[documentation](domain/job-execution-storage.md)
