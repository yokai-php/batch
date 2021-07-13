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
    /**
     * @phpstan-var T
     */
    private object $object;

    /**
     * @phpstan-param T $object
     */
    public function __construct(object $object)
    {
        $this->object = $object;
    }

    /**
     * @inheritdoc
     */
    public function find($subject): object
    {
        return $this->object;
    }
}
