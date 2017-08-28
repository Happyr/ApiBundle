<?php

namespace Happyr\ApiBundle\DependencyInjection;

use Happyr\ApiBundle\Security\Authentication\Provider\DebugProvider;
use Happyr\ApiBundle\Security\Authentication\Provider\DummyProvider;
use Happyr\ApiBundle\Security\Firewall\DebugListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class HappyrApiExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // add the error map to the error handler
        $wsseProviderId = 'happyr_api.wsse.security.authentication.provider';
        if (!$config['wsse']['enabled']) {
            $container->removeDefinition($wsseProviderId);
            $container->register($wsseProviderId, DummyProvider::class)
                ->addArgument(null);
        } elseif ($config['wsse']['debug']) {
            $container->removeDefinition($wsseProviderId);
            $container->register($wsseProviderId, DebugProvider::class)
                ->addArgument(null)
                ->addMethodCall('setDebugRoles', [empty($config['wsse']['debug_roles']) ? ['ROLE_USER', 'ROLE_API_USER'] : $config['wsse']['debug_roles']]);
            $container->getDefinition('happyr_api.wsse.security.authentication.listener')
                ->setClass(DebugListener::class);
        } else {
            $definition = $container->getDefinition($wsseProviderId);
            $definition->replaceArgument(0, new Reference($config['wsse']['user_provider']));
            $definition->replaceArgument(1, new Reference($config['wsse']['cache_service']));
            $definition->replaceArgument(2, $config['wsse']['lifetime']);
        }
    }
}
