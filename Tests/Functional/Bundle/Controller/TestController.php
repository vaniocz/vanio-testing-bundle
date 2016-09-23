<?php
namespace Vanio\TestingBundle\Tests\Functional\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/test")
 */
class TestController extends Controller
{
    /**
     * @Route(name="test_test", defaults={"_format": "html"})
     */
    public function testAction(): Response
    {
        return new Response('
            <html>
                <head>
                    <title>Title</title>
                </head>
                <body>
                    <h1>Heading</h1>
                    <p><a href="/">Link</a></p>
                </body>
            </html>
        ');
    }
}
