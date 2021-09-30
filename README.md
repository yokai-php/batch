# Yokai Batch

[![Latest Stable Version](https://img.shields.io/packagist/v/yokai/batch?style=flat-square)](https://packagist.org/packages/yokai/batch)
[![Downloads Monthly](https://img.shields.io/packagist/dm/yokai/batch?style=flat-square)](https://packagist.org/packages/yokai/batch)

Batch architecture library inspired by Spring Batch.


# Features

- :bookmark_tabs: keep track of the execution of your jobs
- :rocket: base classes to handle batch processing jobs
- :recycle: decoupled reusable components to compose your jobs
- :factory: bridges with popular libraries and frameworks


## :warning: BETA

This library is following [semver](https://semver.org/).
However before we reach the first stable version (`v1.0.0`), we may decide to introduce **API changes in minor versions**.
This is why you should stick to a `v0.[minor].*` requirement !


# Installation

```
composer require yokai/batch
```


## Documentation

Let's [get started](docs/getting-started.md) around core concepts of this library.

Looking for something in particular ?

- [How to use *Aware interfaces ?](docs/recipes/aware-interfaces.md)
- [Create your own job execution storage](docs/recipes/custom-job-execution-storage.md)

Looking for something more specific ?

- [Read/Write from/to CSV/ODS/XLSX](https://github.com/yokai-php/batch-box-spout)
- [Store job executions in relational database](https://github.com/yokai-php/batch-doctrine-dbal)
- [Read from Doctrine ORM entities](https://github.com/yokai-php/batch-doctrine-orm)
- [Write to Doctrine ORM/ODM... objects](https://github.com/yokai-php/batch-doctrine-persistence)
- [Trigger async jobs using CLI command](https://github.com/yokai-php/batch-symfony-console): 
- [Integration with Symfony framework](https://github.com/yokai-php/batch-symfony-framework)
- [Trigger async jobs using using queue](https://github.com/yokai-php/batch-symfony-messenger): 
- [Normalize/Denormalize job items with](https://github.com/yokai-php/batch-symfony-serializer)
- [Validate & Skip invalid items](https://github.com/yokai-php/batch-symfony-validator)


## Contribution

This package is a readonly split of a [larger repository](https://github.com/yokai-php/batch-src),
containing all tests and sources for all librairies of the batch universe.

Please feel free to open an [issue](https://github.com/yokai-php/batch-src/issues)
or a [pull request](https://github.com/yokai-php/batch-src/pulls)
in the [main repository](https://github.com/yokai-php/batch-src).

The library was originally created by [Yann Eugoné](https://github.com/yann-eugone).
See the list of [contributors](https://github.com/yokai-php/batch-src/contributors).


## License

This library is under MIT [LICENSE](LICENSE).
