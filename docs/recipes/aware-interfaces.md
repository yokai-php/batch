# *Aware interfaces

When a job execution starts, a [JobExecution](../../src/JobExecution.php) is created for it.
This object contains information about the current execution.

You will often want to access this object or one of its child to :
- access provided user parameters in your components
- leave some information on the job execution: logs, summary, warning...

To do that, your component will need to implement an interface, telling the library that you need something.

## What is `JobExecutionAwareInterface` ?

The [JobExecutionAwareInterface](../../src/Job/JobExecutionAwareInterface.php)
will allow you to gain access to the current [JobExecution](../../src/JobExecution.php).

> **note:** this interface is covered by [JobExecutionAwareTrait](../../src/Job/JobExecutionAwareTrait.php)
> for a default implementation that is most of the time sufficient.

## What is `JobParametersAwareInterface` ?

The [JobParametersAwareInterface](../../src/Job/JobParametersAwareInterface.php)
will allow you to gain access to the [JobParameters](../../src/JobParameters.php) of the current execution.

> **note:** this interface is covered by [JobParametersAwareTrait](../../src/Job/JobParametersAwareTrait.php)
> for a default implementation that is most of the time sufficient.

## What is `SummaryAwareInterface` ?

The [SummaryAwareInterface](../../src/Job/SummaryAwareInterface.php)
will allow you to gain access to the [Summary](../../src/Summary.php) of the current execution.

> **note:** this interface is covered by [SummaryAwareTrait](../../src/Job/SummaryAwareTrait.php)
> for a default implementation that is most of the time sufficient.

## How does that work exactly ?

There is no magic involved here, 
every component is responsible for propagating the context through these interfaces.

In the library, you will find that :
- [ItemJob](../../src/Job/Item/ItemJob.php) is propagating context to
  [ItemReaderInterface](../../src/Job/Item/ItemReaderInterface.php),
  [ItemProcessorInterface](../../src/Job/Item/ItemProcessorInterface.php) and
  [ItemWriterInterface](../../src/Job/Item/ItemWriterInterface.php).7
- Every
  [ItemReaderInterface](../../src/Job/Item/ItemReaderInterface.php),
  [ItemProcessorInterface](../../src/Job/Item/ItemProcessorInterface.php) and
  [ItemWriterInterface](../../src/Job/Item/ItemWriterInterface.php) 
  acting as a decorator, is propagating context to their decorated element.

You can add this interface to any class, but you are responsible for the context propagation.

## On the same subject

- [What is an ItemJob ?](../domain/item-job.md)
