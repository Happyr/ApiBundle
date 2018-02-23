<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Happyr\ApiBundle\Tests\Functional;

use Happyr\ApiBundle\HappyrApiBundle;
use League\Fractal\Manager;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Symfony\Bundle\SecurityBundle\SecurityBundle;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return HappyrApiBundle::class;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->addCompilerPass(new PublicServicePass('|happyr_api.*|'));
        $kernel = $this->createKernel();
        $kernel->addBundle(SecurityBundle::class);

        $kernel->addConfigFile(__DIR__.'/config.yml');
    }

    public function testFactoriesWithWithDefaultConfiguration()
    {
        $this->bootKernel();
        $container = $this->getContainer();
        $this->assertInstanceOf(Manager::class, $container->get('happyr_api.fractal'));
    }
}
