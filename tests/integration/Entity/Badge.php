<?php

namespace Yokai\Batch\Tests\Integration\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Badge
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var string
     * @ORM\Column()
     */
    public $label;

    /**
     * @var string
     * @ORM\Column(type="integer")
     */
    public $rank;
}
