<?php declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Validator;

use DateTimeInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Yokai\Batch\Job\Item\InvalidItemException;
use Yokai\Batch\Job\Item\ItemProcessorInterface;

final class SkipInvalidItemProcessor implements ItemProcessorInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var array|null
     */
    private $groups;

    public function __construct(ValidatorInterface $validator, array $groups = null)
    {
        $this->validator = $validator;
        $this->groups = $groups;
    }

    /**
     * @inheritDoc
     */
    public function process($item)
    {
        $violations = $this->validator->validate($item, null, $this->groups);
        if (count($violations) === 0) {
            return $item;
        }

        $issues = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $issues[] = sprintf(
                '%s: %s: %s',
                $violation->getPropertyPath(),
                $violation->getMessage(),
                $this->normalizeInvalidValue($violation->getInvalidValue())
            );
        }

        throw new InvalidItemException(implode(PHP_EOL, $issues));
    }

    /**
     * @param mixed $invalidValue
     *
     * @return integer|float|string|boolean
     */
    private function normalizeInvalidValue($invalidValue)
    {
        if ($invalidValue === '') {
            return '""';
        }
        if ($invalidValue === null) {
            return 'NULL';
        }
        if (is_scalar($invalidValue)) {
            return $invalidValue;
        }

        if (is_iterable($invalidValue)) {
            $invalidValues = [];
            foreach ($invalidValue as $value) {
                $invalidValues[] = $this->normalizeInvalidValue($value);
            }

            return implode(', ', $invalidValues);
        }

        if (is_object($invalidValue)) {
            if ($invalidValue instanceof DateTimeInterface) {
                return $invalidValue->format(DateTimeInterface::ISO8601);
            }

            if (method_exists($invalidValue, '__toString')) {
                return (string)$invalidValue;
            }

            return sprintf('%s:%s', get_class($invalidValue), spl_object_hash($invalidValue));
        }

        return gettype($invalidValue);
    }
}
