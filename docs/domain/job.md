# Job

todo

```php
use Yokai\Batch\JobExecution;
use Yokai\Batch\Job\AbstractJob;

class DoStuffJob extends AbstractJob
{
    protected function doExecute(JobExecution $jobExecution) : void
    {
        // you stuff here
    }
}
```
