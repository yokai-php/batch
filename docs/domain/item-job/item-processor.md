# What is an item processor ?

The item processor is used by the item job to transform every read item.

It can be any class implementing [ItemProcessorInterface](../../../src/Job/Item/ItemProcessorInterface.php).

## What types of item processors exists ?

**Built-in item processors:**
- [NullProcessor](../../../src/Job/Item/Processor/NullProcessor.php):
  perform no transformation on items.
- [ChainProcessor](../../../src/Job/Item/Processor/ChainProcessor.php):
  chain transformation of multiple item processor, one after the other.

**Item processors from bridges:**
- [SkipInvalidItemProcessor (`symfony/validator`)](https://github.com/yokai-php/batch-symfony-validator/blob/0.x/src/SkipInvalidItemProcessor.php):
  validate item and throw exception if invalid that will cause item to be skipped.
- [DenormalizeItemProcessor (`symfony/serializer`)](https://github.com/yokai-php/batch-symfony-serializer/blob/0.x/src/DenormalizeItemProcessor.php):
  denormalize each item.
- [NormalizeItemProcessor (`symfony/serializer`)](https://github.com/yokai-php/batch-symfony-validator/blob/0.x/src/NormalizeItemProcessor.php):
  normalize each item.

## On the same subject

- [What is an item job ?](../item-job.md)
