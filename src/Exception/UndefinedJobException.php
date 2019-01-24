<?php declare(strict_types=1);

namespace Yokai\Batch\Exception;

use DomainException;

class UndefinedJobException extends DomainException
{
    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Job "%s" is undefined', $name));
    }
}
