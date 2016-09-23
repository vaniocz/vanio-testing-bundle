<?php
namespace Vanio\TestingBundle\Tests\Functional\Bundle\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vanio\TestingBundle\Tests\Functional\Bundle\Entity\Article;

class LoadArticles extends ContainerAwareFixture
{
    public function load(ObjectManager $objectManager)
    {
        $objectManager->persist(new Article('title'));
        $objectManager->flush();
    }

    /**
     * @return ContainerInterface|null
     */
    public function container()
    {
        return $this->container;
    }
}
