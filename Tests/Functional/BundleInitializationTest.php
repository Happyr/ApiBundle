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

use Cache\Adapter\Apc\ApcCachePool;
use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Adapter\Chain\CachePoolChain;
use Cache\Adapter\Doctrine\DoctrineCachePool;
use Cache\Adapter\Memcache\MemcacheCachePool;
use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Predis\PredisCachePool;
use Cache\Adapter\Redis\RedisCachePool;
use Cache\Adapter\Void\VoidCachePool;
use Cache\AdapterBundle\CacheAdapterBundle;
use Cache\Namespaced\NamespacedCachePool;
use Cache\Prefixed\PrefixedCachePool;
use Happyr\ApiBundle\HappyrApiBundle;
use League\Fractal\Manager;
use Nyholm\BundleTest\BaseBundleTestCase;
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
