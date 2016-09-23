<?php
namespace Vanio\TestingBundle\Tests\Functional;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Vanio\TestingBundle\TestCase\WebTestCase;

class PageRenderingTest extends WebTestCase
{
    function test_page_renders_successfully()
    {
        $response = $this->request('GET', '/test');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertHtmlContainsText(
            '
                HEADING
                Link [/]
            ',
            $response->getContent()
        );
        $this->assertHtmlNotContainsText('foo', $response->getContent());
    }

    function test_crawling_page()
    {
        $this->request('GET', '/test');
        $this->assertInstanceOf(Crawler::class, $this->crawler);
        $this->assertSame(1, $this->crawler->filter('html:contains("Heading")')->count());
    }
}
