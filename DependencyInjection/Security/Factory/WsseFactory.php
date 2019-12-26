<?php

namespace Happyr\ApiBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

/**
 * Goes through the configuration and configures the WsseListener.
 *
 * @author Toby Ryuk
 */
class WsseFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, string $id, array $config, string $userProvider, ?string $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.wsse.'.$id;
        $container
            ->setDefinition($providerId, new ChildDefinition('happyr_api.wsse.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'security.authentication.listener.wsse.'.$id;
        $container->setDefinition($listenerId, new ChildDefinition('happyr_api.wsse.security.authentication.listener'));

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'wsse';
    }

    /**
     * @param NodeDefinition $node
     */
    public function addConfiguration(NodeDefinition $node)
    {
    }
}
