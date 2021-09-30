# Item Job

## What is an item ?

The library allows you to declare and execute jobs, but wait why do we named it batch then ?
There you are, the `ItemJob` is where batch processing actually starts.

This is just a job that has been prepared to batch handle items.

If you are familiar with the concept of an [ETL](https://en.wikipedia.org/wiki/Extract,_transform,_load), 
this is pretty much the same.

The item job allows you to split your logic into 3 different component :
- an item [reader](item-job/item-reader.md): stands for **Extract** in ETL
- an item [processor](item-job/item-processor.md): stands for **Transform** in ETL
- an item [writer](item-job/item-writer.md): stands for **Load** in ETL

## How to create an item job ?

```php
<?php

declare(strict_types=1);

use Yokai\Batch\Job\Item\ItemJob;
use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Storage\NullJobExecutionStorage;

class ItemReader implements ItemReaderInterface
{
    public function read(): iterable
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
    public function write(iterable $items): void
    {
        file_put_contents(__DIR__ . '/file.log', 'write :' . PHP_EOL, FILE_APPEND);
        foreach ($items as $item) {
            file_put_contents(__DIR__ . '/file.log', print_r($item, true) . PHP_EOL, FILE_APPEND);
        }
    }
}

$job = new ItemJob(2, new ItemReader(), new ItemProcessor(), new ItemWriter(), new NullJobExecutionStorage());
```

## On the same subject

- [What is a Job ?](job.md)
- [How do I start a job ?](job-launcher.md)
