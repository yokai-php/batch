<?php declare(strict_types=1);

namespace Yokai\Batch\Bridge\Doctrine\Common;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Yokai\Batch\Job\Item\ItemWriterInterface;

final class ObjectWriter implements ItemWriterInterface
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var ObjectManager[]
     */
    private $encounteredManagers = [];

    /**
     * @var ObjectManager[]
     */
    private $managerForClass = [];

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @inheritDoc
     */
    public function write(iterable $items): void
    {
        foreach ($items as $item) {
            $this->getManagerForClass($item)->persist($item);
        }

        foreach ($this->encounteredManagers as $manager) {
            $manager->flush();
        }

        foreach ($items as $item) {
            $this->getManagerForClass($item)->detach($item);
        }

        $this->encounteredManagers = [];
    }

    private function getManagerForClass($item): ObjectManager
    {
        if (!is_object($item)) {
            throw $this->createInvalidItemException($item);
        }

        $class = get_class($item);
        $manager = $this->managerForClass[$class] ?? null;
        if ($manager instanceof ObjectManager) {
            $this->encounteredManagers[spl_object_id($manager)] = $manager;

            return $this->managerForClass[$class];
        }

        $manager = $this->doctrine->getManagerForClass($class);
        if ($manager === null) {
            throw $this->createInvalidItemException($item);
        }

        $this->managerForClass[$class] = $manager;

        return $manager;
    }

    private function createInvalidItemException($item): \LogicException
    {
        return new \LogicException(
            sprintf(
                'Items to write must be object managed by Doctrine. Got "%s".',
                is_object($item) ? get_class($item) : gettype($item)
            )
        );
    }
}
