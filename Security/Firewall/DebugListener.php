<?php

namespace Happyr\ApiBundle\Security\Firewall;

use Happyr\ApiBundle\Service\ResponseFactory;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Happyr\ApiBundle\Security\Authentication\Token\WsseUserToken;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class DebugListener implements ListenerInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * WsseListener constructor.
     *
     * @param TokenStorageInterface          $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     */
    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        $token = new WsseUserToken();
        $token->setUser('api_user');

        $authToken = $this->authenticationManager->authenticate($token);
        $this->tokenStorage->setToken($authToken);
    }

    /**
     * @param ResponseFactory $responseFactory
     **/
    public function setResponseFactory(ResponseFactory $responseFactory)
    {
    }
}
