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
            $manager = $this->getManager($item);
            if (!$manager->contains($item)) {
                $manager->persist($item);
            }
        }

        foreach ($this->encounteredManagers as $manager) {
            $manager->flush();
        }

        foreach ($items as $item) {
            $this->getManager($item)->detach($item);
        }

        $this->encounteredManagers = [];
    }

    private function getManager($item): ObjectManager
    {
        $invalidItemException = new \LogicException(
            sprintf(
                'Items to write must be object managed by Doctrine. Got "%s".',
                is_object($item) ? get_class($item) : gettype($item)
            )
        );

        if (!is_object($item)) {
            throw $invalidItemException;
        }

        $manager = $this->doctrine->getManagerForClass(get_class($item));
        if ($manager === null) {
            throw $invalidItemException;
        }

        $managerId = spl_object_hash($manager);
        if (!isset($this->encounteredManagers[$managerId])) {
            $this->encounteredManagers[$managerId] = $manager;
        }

        return $manager;
    }
}
