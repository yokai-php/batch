# What is an item reader ?

The item reader is used by the item job to extract item from a source.

It can be any class implementing [ItemReaderInterface](../../../src/Job/Item/ItemReaderInterface.php).

## What types of item readers exists ?

**Built-in item readers:**
- [StaticIterableReader](../../../src/Job/Item/Reader/StaticIterableReader.php):
  read from an iterable you provide during construction.
- [SequenceReader](../../../src/Job/Item/Reader/SequenceReader.php):
  read from multiple item reader, one after the other.

**Item readers from bridges:**
- [FlatFileReader (`box/spout`)](https://github.com/yokai-php/batch-box-spout/blob/0.x/src/FlatFileReader.php):
  read from any CSV/ODS/XLSX file.
- [EntityReader (`doctrine/orm`)](https://github.com/yokai-php/batch-doctrine-orm/blob/0.x/src/EntityReader.php):
  read from any Doctrine ORM entity.

## On the same subject

- [What is an item job ?](../item-job.md)
