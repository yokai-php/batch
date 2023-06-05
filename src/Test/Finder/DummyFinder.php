<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Finder;

use Yokai\Batch\Finder\FinderInterface;

/**
 * This finder return always the same component.
 *
 * @psalm-template T of object
 * @template-implements FinderInterface<T>
 */
final class DummyFinder implements FinderInterface
{
    public function __construct(
        /**
         * @phpstan-var T
         */
        private object $object,
    ) {
    }

    public function find(mixed $subject): object
    {
        return $this->object;
    }
}
