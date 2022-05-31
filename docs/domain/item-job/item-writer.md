# What is an item writer ?

The item writer is used by the item job to load every processed item.

It can be any class implementing [ItemWriterInterface](../../../src/Job/Item/ItemWriterInterface.php).

## What types of item writers exists ?

**Built-in item writers:**
- [JsonLinesWriter](../../../src/Job/Item/Writer/Filesystem/JsonLinesWriter.php):
  write items as a json string each on a line of a file.
- [ChainWriter](../../../src/Job/Item/Writer/ChainWriter.php):
  write items on multiple item writers.
- [ConditionalWriter](../../../src/Job/Item/Writer/ConditionalWriter.php):
  will only write items that are matching your conditions.
- [NullWriter](../../../src/Job/Item/Writer/NullWriter.php):
  do not write items.
- [RoutingWriter](../../../src/Job/Item/Writer/RoutingWriter.php):
  route writing to different writer based on your logic.
- [SummaryWriter](../../../src/Job/Item/Writer/SummaryWriter.php):
  write items to a job summary value.

**Item writers from bridges:**
- [DispatchEachItemAsMessageWriter (`symfony/messenger`)](https://github.com/yokai-php/batch-symfony-messenger/blob/0.x/src/Writer/DispatchEachItemAsMessageWriter.php):
  dispatch each item as a message in a bus.
- [DoctrineDBALInsertWriter (`doctrine/dbal`)](https://github.com/yokai-php/batch-doctrine-dbal/blob/0.x/src/DoctrineDBALInsertWriter.php):
  write items by inserting in a table via a Doctrine `Connection`.
- [DoctrineDBALUpsertWriter (`doctrine/dbal`)](https://github.com/yokai-php/batch-doctrine-dbal/blob/0.x/src/DoctrineDBALUpsertWriter.php):
  write items by inserting/updating in a table via a Doctrine `Connection`.
- [ObjectWriter (`doctrine/persistence`)](https://github.com/yokai-php/batch-doctrine-persistence/blob/0.x/src/ObjectWriter.php):
  write items to any Doctrine `ObjectManager`.
- [FlatFileWriter (`box/spout`)](https://github.com/yokai-php/batch-box-spout/blob/0.x/src/Writer/FlatFileWriter.php):
  write items to any CSV/ODS/XLSX file.

## On the same subject

- [What is an item job ?](../item-job.md)
