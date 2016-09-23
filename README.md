# [<img alt="Vanio" src="http://www.vanio.cz/img/vanio-logo.png" width="130" align="top">](http://www.vanio.cz) Testing Bundle

[![Build Status](https://travis-ci.org/vaniocz/vanio-testing-bundle.svg?branch=master)](https://travis-ci.org/vaniocz/vanio-testing-bundle)
[![Coverage Status](https://coveralls.io/repos/github/vaniocz/vanio-testing-bundle/badge.svg?branch=master)](https://coveralls.io/github/vaniocz/vanio-testing-bundle?branch=master)
![PHP7](https://img.shields.io/badge/php-7-6B7EB9.svg)
[![License](https://poser.pugx.org/vanio/vanio-testing-bundle/license)](https://github.com/vaniocz/vanio-testing-bundle/blob/master/LICENSE)

A Symfony2 Bundle primarily helping you with integration testing.

# Installation
Installation can be done as usually using composer.
`composer require vanio/vanio-testing-bundle`

In case of testing bundle configuration you need to extend prepared `Vanio\TestingBundle\HttpKernel\TestKernel` class.
It provides you access to `TestContainerBuilder` you can use when testing bundle configurations.
This kernel is also pre-configured for easier creation of standalone bundle's testing environment.
```php
<?php
// AppKernel.php

use Vanio\TestingBundle\HttpKernel\TestKernel;

class AppKernel extends TestKernel
{}
```

The default implementation of `TestKernel` class expects you to create a file named
`Tests/Functional/app/{environment}/bundles.php` relative to the kernel's root directory returning an array of
the bundles to register. You can specify the environment later inside your test case.
```php
<?php
// Tests/Functional/app/default/bundles.php

return [
    new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle,
    new Symfony\Bundle\FrameworkBundle\FrameworkBundle,
    new Vanio\TestingBundle\Tests\Functional\Bundle\TestBundle,
];
```

Similarly it expects you to create a configuration file named `Tests/Functional/app/{environment}/config.yml` where
you can configure the bundles.
```yml
<?php
// Tests/Functional/app/default/config.yml

return [
    new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle,
    new Symfony\Bundle\FrameworkBundle\FrameworkBundle,
    new Vanio\TestingBundle\Tests\Functional\Bundle\TestBundle,
];
```

Otherwise you can register this bundle classically inside your `AppKernel`.
```php
// app/AppKernel.php
// ...

class AppKernel extends Kernel
{
    // ...

    public function registerBundles(): array
    {
        // ...

        if ($this->environment === 'test') {
            $bundles[] = new Vanio\TestingBundle\VanioTestingBundle;
        }

        // ...
    }
}
```

The following examples are taken from actual tests of this bundle.

# Testing Configuration of Bundle Extensions
```php
// Tests/Functional/BundleConfigurationTest.php
// ...

use Vanio\TestingBundle\DependencyInjection\TestContainerBuilder;
use Vanio\TestingBundle\PhpUnit\KernelTestCase;

class ExtensionConfigurationTest extends KernelTestCase
{
    function test_extension_configuration()
    {
        self::boot();
        $this->assertInstanceOf(TestContainerBuilder::class, self::$container);
        $this->assertArraySubset(
            [
                'secret' => 'secret',
                'form' => ['enabled' => false],
                'profiler' => ['enabled' => false],
                'router' => ['enabled' => true],
            ],
            self::$container->processExtensionConfig('framework')
        );
    }
}
```

# Testing of Page Rendering
```php
// Tests/Functional/PageRenderingTest.php
// ...

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Vanio\TestingBundle\PhpUnit\WebTestCase;

class PageRenderingTest extends WebTestCase
{
    function test_page_renders_successfully()
    {
        self::boot();
        $response = $this->request('GET', '/test');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertHtmlContainsText(
            '
                HEADING
                Paragraph
                Link [/]
            ',
            $response->getContent()
        );
        $this->assertHtmlNotContainsText('foo', $response->getContent());
    }

    function test_crawling_page()
    {
        self::boot();
        $this->request('GET', '/test');
        $this->assertInstanceOf(Crawler::class, $this->crawler);
        $this->assertSame(1, $this->crawler->filter('html:contains("Heading")')->count());
    }
}
```
