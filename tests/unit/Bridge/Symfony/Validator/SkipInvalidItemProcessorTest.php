<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Symfony\Validator;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Yokai\Batch\Bridge\Symfony\Validator\SkipInvalidItemProcessor;
use Yokai\Batch\Job\Item\InvalidItemException;

class SkipInvalidItemProcessorTest extends TestCase
{
    /**
     * @dataProvider groups
     */
    public function testProcessValid(array $groups = null): void
    {
        /** @var ObjectProphecy|ValidatorInterface $validator */
        $validator = $this->prophesize(ValidatorInterface::class);
        $validator->validate('item to validate', null, $groups)
            ->shouldBeCalledTimes(1)
            ->willReturn(new ConstraintViolationList([]));

        $processor = new SkipInvalidItemProcessor($validator->reveal(), $groups);
        self::assertSame('item to validate', $processor->process('item to validate'));
    }

    /**
     * @dataProvider groups
     * @expectedException \Yokai\Batch\Job\Item\InvalidItemException
     */
    public function testProcessInvalid(array $groups = null): void
    {
        $violations = new ConstraintViolationList([]);
        /** @var ObjectProphecy|ConstraintViolationInterface $stringViolation */
        $stringViolation = $this->prophesize(ConstraintViolationInterface::class);
        $stringViolation->getPropertyPath()->willReturn('stringProperty');
        $stringViolation->getInvalidValue()->willReturn('invalid string');
        $stringViolation->getMessage()->willReturn('"invalid string" is invalid');

        /** @var ObjectProphecy|ConstraintViolationInterface $dateViolation */
        $dateViolation = $this->prophesize(ConstraintViolationInterface::class);
        $dateViolation->getPropertyPath()->willReturn('dateProperty');
        $dateViolation->getInvalidValue()->willReturn(new \DateTime());
        $dateViolation->getMessage()->willReturn('"invalid date" is invalid');

        /** @var ObjectProphecy|ConstraintViolationInterface $objectToStringViolation */
        $objectToStringViolation = $this->prophesize(ConstraintViolationInterface::class);
        $objectToStringViolation->getPropertyPath()->willReturn('objectToStringProperty');
        $objectToStringViolation->getInvalidValue()->willReturn(new class { function __toString(){return 'invalid object';}});
        $objectToStringViolation->getMessage()->willReturn('"object with __toString" is invalid');

        /** @var ObjectProphecy|ConstraintViolationInterface $dateViolation */
        $objectViolation = $this->prophesize(ConstraintViolationInterface::class);
        $objectViolation->getPropertyPath()->willReturn('objectProperty');
        $objectViolation->getInvalidValue()->willReturn(new \stdClass());
        $objectViolation->getMessage()->willReturn('"object" is invalid');

        /** @var ObjectProphecy|ConstraintViolationInterface $arrayViolation */
        $arrayViolation = $this->prophesize(ConstraintViolationInterface::class);
        $arrayViolation->getPropertyPath()->willReturn('arrayProperty');
        $arrayViolation->getInvalidValue()->willReturn([null, new \stdClass(), [1, new \DateTime(), new \ArrayIterator(['string', 2.3])]]);
        $arrayViolation->getMessage()->willReturn('"array" is invalid');

        $violations->add($stringViolation->reveal());
        $violations->add($dateViolation->reveal());
        $violations->add($objectToStringViolation->reveal());
        $violations->add($objectViolation->reveal());
        $violations->add($arrayViolation->reveal());

        /** @var ObjectProphecy|ValidatorInterface $validator */
        $validator = $this->prophesize(ValidatorInterface::class);
        $validator->validate('item to validate', null, $groups)
            ->shouldBeCalledTimes(1)
            ->willReturn($violations);

        $processor = new SkipInvalidItemProcessor($validator->reveal(), $groups);
        $processor->process('item to validate');
    }

    public function groups()
    {
        yield [null];
        yield [[]];
        yield [['Full']];
    }
}
