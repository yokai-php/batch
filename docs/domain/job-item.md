# Job Item

todo

```php
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\Item\ItemJob;
use Yokai\Batch\Storage\NullJobExecutionStorage;

class ItemReader implements ItemReaderInterface
{
    public function read() : iterable
    {
        yield '1';
        yield '2';
        yield '3';
    }
}
class ItemProcessor implements ItemProcessorInterface
{
    public function process($item)
    {
        return intval($item);
    }
}
class ItemWriter implements ItemWriterInterface
{
    public function write(iterable $items) : void
    {
        file_put_contents(__DIR__.'/file.log', 'write :'.PHP_EOL, FILE_APPEND);
        foreach ($items as $item) {
            file_put_contents(__DIR__.'/file.log', print_r($item, true).PHP_EOL, FILE_APPEND);
        }
    }
}

$job = new ItemJob(2, new ItemReader(), new ItemProcessor(), new ItemWriter(), new NullJobExecutionStorage());
$job->execute((new JobExecutionFactory())->create('job.name'));
```
