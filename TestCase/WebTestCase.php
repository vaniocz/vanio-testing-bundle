<?php
namespace Vanio\TestingBundle\TestCase;

use Html2Text\Html2Text;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Vanio\Stdlib\Strings;
use Vanio\Stdlib\Uri;

class WebTestCase extends KernelTestCase
{
    /** @var Crawler|null */
    protected $crawler;

    protected function request(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        string $content = null,
        bool $changeHistory = true
    ): Response {
        static::rebootKernel();
        $client = self::getClient();

        if (!Strings::startsWith($uri, '/')) {
            $u = new Uri(self::getRouter()->generate($uri, $parameters));
            $uri = $u->path();

            if (strtoupper($method) === 'GET') {
                $parameters = array_intersect_assoc($parameters, $u->queryParameters());
            }
        }

        $this->crawler = $client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);

        return $client->getResponse();
    }

    /**
     * @param string|string[] $text
     * @param string $html
     */
    protected function assertHtmlContainsText($text, string $html)
    {
        $haystack = $this->convertHtmlToText($html);

        foreach ((array) $text as $needle) {
            $this->assertContains($this->convertHtmlToText($needle), $haystack);
        }
    }

    /**
     * @param string|string[] $text
     * @param string $html
     */
    protected function assertHtmlNotContainsText($text, string $html)
    {
        $haystack = $this->convertHtmlToText($html);

        foreach ((array) $text as $needle) {
            $this->assertNotContains($this->convertHtmlToText($needle), $haystack);
        }
    }

    private static function getClient(): Client
    {
        return self::getContainer()->get('test.client');
    }

    private static function getRouter(): RouterInterface
    {
        return self::getContainer()->get('router');
    }

    /**
     * @param string $html
     * @return string
     */
    private function convertHtmlToText(string $html): string
    {
        $text = (new Html2Text($html))->getText();

        return $this->normalizeWhitespace($text);
    }

    private function normalizeWhitespace(string $text): string
    {
        return preg_replace('~\s+~', ' ', $text);
    }
}
