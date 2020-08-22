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

Before starting, please have a look to [domain](docs/1-domain.md) documentation.
While reading this section you will learn core concepts and vocabulary.


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
