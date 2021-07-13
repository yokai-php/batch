<?php

declare(strict_types=1);

namespace Yokai\Batch\Finder;

/**
 * A finder is a component that is able to
 * filter out the appropriate subcomponent from a list.
 *
 * @phpstan-template T of object
 */
interface FinderInterface
{
    /**
     * @param mixed $subject
     *
     * @return mixed
     * @phpstan-return T
     */
    public function find($subject): object;
}
