# Item Job

## What is an item ?

The library allows you to declare and execute jobs, but wait why did you named it batch then ?
There you are, the Item Job is where batch processing actually starts.

This is just a job that has been prepared to batch handle items.

If you are familiar with the concept of an [ETL](https://en.wikipedia.org/wiki/Extract,_transform,_load), 
this is pretty much the same.

The item job allows you to split your logic into 3 different component :
- a reader: stands for Extract in ETL
- a processor: stands for Transform in ETL
- a writer: stands for Load in ETL

### What is an item reader ?

The item reader responsibility is to extract data from a source.

The item reader can be any class that implements [ItemReaderInterface](../../src/Job/Item/ItemReaderInterface.php).

**Built-in item readers:**
- [StaticIterableReader](../../src/Job/Item/Reader/StaticIterableReader.php):
  read from an iterable you provide during construction.
- [SequenceReader](../../src/Job/Item/Reader/SequenceReader.php):
  read from multiple item reader, one after the other.

**Item readers from bridges:**
- [FlatFileReader (`box/spout`)](https://github.com/yokai-php/batch-box-spout/blob/0.x/src/FlatFileReader.php):
  read from any CSV/ODS/XLSX file.
- [EntityReader (`doctrine/orm`)](https://github.com/yokai-php/batch-doctrine-orm/blob/0.x/src/EntityReader.php):
  read from any Doctrine ORM entity.

### What is an item processor ?

The item processor responsibility is to transform every read item.

The item processor can be any class that implements [ItemProcessorInterface](../../src/Job/Item/ItemProcessorInterface.php).

**Built-in item processors:**
- [NullProcessor](../../src/Job/Item/Processor/NullProcessor.php):
  perform no transformation on items.
- [ChainProcessor](../../src/Job/Item/Processor/ChainProcessor.php):
  chain transformation of multiple item processor, one after the other.

**Item processors from bridges:**
- [SkipInvalidItemProcessor (`symfony/validator`)](https://github.com/yokai-php/batch-symfony-validator/blob/0.x/src/SkipInvalidItemProcessor.php):
  validate item and throw exception if invalid that will cause item to be skipped.
- [DenormalizeItemProcessor (`symfony/serializer`)](https://github.com/yokai-php/batch-symfony-serializer/blob/0.x/src/DenormalizeItemProcessor.php):
  denormalize each item.
- [NormalizeItemProcessor (`symfony/serializer`)](https://github.com/yokai-php/batch-symfony-validator/blob/0.x/src/NormalizeItemProcessor.php):
  normalize each item.

### What is an item writer ?

The item processor responsibility is to load every processed item.

The item writer can be any class that implements [ItemWriterInterface](../../src/Job/Item/ItemWriterInterface.php).

**Built-in item writers:**
- [NullWriter](../../src/Job/Item/Writer/NullWriter.php):
  do not write items.
- [NullWriter](../../src/Job/Item/Writer/ChainWriter.php):
  write items on multiple item writers.

**Item writers from bridges:**
- [ObjectWriter (`doctrine/persistence`)](https://github.com/yokai-php/batch-doctrine-persistence/blob/0.x/src/ObjectWriter.php):
  write items to any Doctrine `ObjectManager`.
- [FlatFileWriter (`box/spout`)](https://github.com/yokai-php/batch-box-spout/blob/0.x/src/FlatFileWriter.php):
  write items to any CSV/ODS/XLSX file.

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
