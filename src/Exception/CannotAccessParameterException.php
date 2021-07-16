<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

/**
 * This exception can be thrown by any @see \Yokai\Batch\Job\Parameters\JobParameterAccessorInterface.
 * When it is raised, it means that the component was not able to find the requested parameter.
 */
class CannotAccessParameterException extends RuntimeException
{
}
