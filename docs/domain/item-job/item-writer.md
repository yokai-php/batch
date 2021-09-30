# What is an item writer ?

The item writer is used by the item job to load every processed item.

It can be any class implementing [ItemWriterInterface](../../../src/Job/Item/ItemWriterInterface.php).

## What types of item writers exists ?

**Built-in item writers:**
- [NullWriter](../../../src/Job/Item/Writer/NullWriter.php):
  do not write items.
- [ChainWriter](../../../src/Job/Item/Writer/ChainWriter.php):
  write items on multiple item writers.

**Item writers from bridges:**
- [ObjectWriter (`doctrine/persistence`)](https://github.com/yokai-php/batch-doctrine-persistence/blob/0.x/src/ObjectWriter.php):
  write items to any Doctrine `ObjectManager`.
- [FlatFileWriter (`box/spout`)](https://github.com/yokai-php/batch-box-spout/blob/0.x/src/FlatFileWriter.php):
  write items to any CSV/ODS/XLSX file.

## On the same subject

- [What is an item job ?](../item-job.md)
