<?php

declare(strict_types=1);

namespace Yokai\Batch\Bridge\Doctrine\Orm;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Yokai\Batch\Job\Item\ItemReaderInterface;

final class EntityReader implements ItemReaderInterface
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var string
     */
    private $class;

    public function __construct(ManagerRegistry $doctrine, string $class)
    {
        $this->doctrine = $doctrine;
        $this->class = $class;
    }

    /**
     * @inheritDoc
     */
    public function read(): iterable
    {
        $manager = $this->doctrine->getManagerForClass($this->class);
        if (!$manager instanceof EntityManagerInterface) {
            throw new \LogicException(
                sprintf('Provided class must be a valid Doctrine entity. Got "%s".', $this->class)
            );
        }

        $query = $manager->createQueryBuilder()
            ->select('e')
            ->from($this->class, 'e');

        foreach ($query->getQuery()->iterate() as $row) {
            yield $row[0];
        }
    }
}
