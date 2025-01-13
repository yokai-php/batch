<?php

declare(strict_types=1);

namespace Yokai\Batch\Finder;

/**
 * A finder is a component that is able to
 * filter out the appropriate subcomponent from a list.
 *
 * @template T of object
 */
interface FinderInterface
{
    /**
     * Finds the appropriate component for subject.
     *
     * @param mixed $subject The subject that should help to find component
     *
     * @return object The component that matches the subject
     * @return T
     */
    public function find(mixed $subject): object;
}
