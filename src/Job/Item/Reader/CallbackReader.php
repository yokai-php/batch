<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader;

use Yokai\Batch\Job\Item\ItemReaderInterface;

/**
 * An {@see ItemReaderInterface} that read from a {@see Closure} provided at construction.
 *
 * Provided {@see Closure} must accept no argument and must return an iterable of read items.
 */
final readonly class CallbackReader implements ItemReaderInterface
{
    public function __construct(
        /**
         * @var \Closure(): iterable<mixed>
         */
        private \Closure $callback,
    ) {
    }

    public function read(): iterable
    {
        return ($this->callback)();
    }
}
