<?php

namespace Yokai\Batch\Tests\Integration\Processor;

use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\Tests\Integration\Entity\Repository;

final class RepositoryProcessor implements ItemProcessorInterface
{
    public function process($item)
    {
        $repository = new Repository();
        $repository->label = $item['label'];
        $repository->url = $item['url'];

        return $repository;
    }
}
