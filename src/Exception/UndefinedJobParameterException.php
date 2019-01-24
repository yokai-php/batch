<?php declare(strict_types=1);

namespace Yokai\Batch\Exception;

use DomainException;

class UndefinedJobParameterException extends DomainException
{
    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Parameter "%s" is undefined', $name));
    }
}
