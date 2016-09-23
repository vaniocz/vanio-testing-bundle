<?php
namespace Vanio\TestingBundle\Tests\Functional\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class Article
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return int|null
     */
    public function id()
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }
}
