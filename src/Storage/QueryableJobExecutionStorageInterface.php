<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Yokai\Batch\JobExecution;

interface QueryableJobExecutionStorageInterface extends ListableJobExecutionStorageInterface
{
    /**
     * @param Query $query
     *
     * @return iterable|JobExecution[]
     */
    public function query(Query $query): iterable;
}
