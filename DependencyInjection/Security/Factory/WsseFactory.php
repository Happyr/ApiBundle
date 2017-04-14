<?php

namespace Happyr\ApiBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

/**
 * Goes through the configuration and configures the WsseListener
 *
 * @author Toby Ryuk
 */
class WsseFactory implements SecurityFactoryInterface
{
    /**
     * @param ContainerBuilder $container
     * @param int              $id
     * @param array            $config
     * @param string           $userProvider
     * @param string           $defaultEntryPoint
     *
     * @return array
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.wsse.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('happyr_api.wsse.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'security.authentication.listener.wsse.'.$id;
        $container->setDefinition($listenerId, new DefinitionDecorator('happyr_api.wsse.security.authentication.listener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
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
