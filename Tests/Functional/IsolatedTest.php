<?php
namespace Vanio\TestingBundle\Tests\Functional;

use Vanio\TestingBundle\TestCase\IsolatedWebTestCase;
use Vanio\TestingBundle\Tests\Functional\Bundle\DataFixtures\LoadArticles;
use Vanio\TestingBundle\Tests\Functional\Bundle\Entity\Article;

class IsolatedTest extends IsolatedWebTestCase
{
    protected static function setUpFixturesForClass()
    {
        self::loadFixtures([LoadArticles::class]);
    }

    function test_the_same_connection_is_kept_during_kernel_reboots()
    {
        $connection = self::getConnection();
        static::bootKernel();
        $this->assertSame($connection, self::getConnection());
    }

    function test_shared_fixtures_are_always_loaded()
    {
        $this->assertCount(1, self::getEntityRepository(Article::class)->findAll());
        static::bootKernel();
        $this->assertCount(1, self::getEntityRepository(Article::class)->findAll());
    }
}
