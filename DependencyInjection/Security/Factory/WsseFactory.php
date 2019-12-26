<?php

namespace Happyr\ApiBundle\DependencyInjection\Security\Factory;

use Happyr\ApiBundle\Security\Authentication\Provider\WsseProvider;
use Happyr\ApiBundle\Security\Firewall\WsseListener;
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
            ->setDefinition($providerId, new ChildDefinition(WsseProvider::class))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'security.authentication.listener.wsse.'.$id;
        $container->setDefinition($listenerId, new ChildDefinition(WsseListener::class));

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
