<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Doctrine\Common;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\Bridge\Doctrine\Common\ObjectWriter;
use Yokai\Batch\Tests\Unit\Bridge\Doctrine\Group;
use Yokai\Batch\Tests\Unit\Bridge\Doctrine\Product;
use Yokai\Batch\Tests\Unit\Bridge\Doctrine\User;

class ObjectWriterTest extends TestCase
{
    public function testWrite()
    {
        $user1 = new User(1);
        $user2 = new User(2);
        $group1 = new Group(1);

        /** @var ObjectProphecy|ObjectManager $userManager */
        $userManager = $this->prophesize(ObjectManager::class);
        $userManager->persist($user1)
            ->shouldBeCalledTimes(1);
        $userManager->persist($user2)
            ->shouldBeCalledTimes(1);
        $userManager->flush()
            ->shouldBeCalledTimes(1);
        $userManager->detach($user1)
            ->shouldBeCalledTimes(1);
        $userManager->detach($user2)
            ->shouldBeCalledTimes(1);

        /** @var ObjectProphecy|ObjectManager $groupManager */
        $groupManager = $this->prophesize(ObjectManager::class);
        $groupManager->persist($group1)
            ->shouldBeCalledTimes(1);
        $groupManager->flush()
            ->shouldBeCalledTimes(1);
        $groupManager->detach($group1)
            ->shouldBeCalledTimes(1);

        /** @var ObjectProphecy|ObjectManager $productManager */
        $productManager = $this->prophesize(ObjectManager::class);
        $productManager->persist(Argument::any())
            ->shouldNotBeCalled();
        $productManager->flush()
            ->shouldNotBeCalled();
        $productManager->detach(Argument::any())
            ->shouldNotBeCalled();

        /** @var ObjectProphecy|ManagerRegistry $doctrine */
        $doctrine = $this->prophesize(ManagerRegistry::class);
        $doctrine->getManagerForClass(User::class)
            ->shouldBeCalledTimes(1)
            ->willReturn($userManager->reveal());
        $doctrine->getManagerForClass(Group::class)
            ->shouldBeCalledTimes(1)
            ->willReturn($groupManager->reveal());
        $doctrine->getManagerForClass(Product::class)
            ->shouldNotBeCalled()
            ->willReturn($productManager->reveal());

        $writer = new ObjectWriter($doctrine->reveal());
        $writer->write([$user1, $user2, $group1]);
    }

    /**
     * @expectedException \LogicException
     */
    public function testWriteThrowExceptionWithNonObject()
    {
        /** @var ObjectProphecy|ManagerRegistry $doctrine */
        $doctrine = $this->prophesize(ManagerRegistry::class);

        $writer = new ObjectWriter($doctrine->reveal());
        $writer->write(['string']);
    }

    /**
     * @expectedException \LogicException
     */
    public function testWriteThrowExceptionWithNonManagedObjects()
    {
        /** @var ObjectProphecy|ManagerRegistry $doctrine */
        $doctrine = $this->prophesize(ManagerRegistry::class);
        $doctrine->getManagerForClass(User::class)
            ->willReturn(null);

        $writer = new ObjectWriter($doctrine->reveal());
        $writer->write([new User(1)]);
    }
}
