<?php

namespace Happyr\ApiBundle\Security\Firewall;

use Happyr\ApiBundle\Service\ResponseFactory;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Happyr\ApiBundle\Security\Authentication\Token\WsseUserToken;

/**
 * Listens for incoming events and checks if they have x-wsse in the header. If not ignore, otherwise, sets up a
 * token and sends it of to validation. If validation passes, stores the token in the cache. If it fails, throw
 * an exception.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class WsseListener
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
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @param TokenStorageInterface          $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     */
    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * @param ResponseFactory $responseFactory
     **/
    public function setResponseFactory(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();

        $wsseRegex = '|UsernameToken Username="([^"]+)", PasswordDigest="([^"]+)", Nonce="([a-zA-Z0-9+/]+={0,2})", Created="([^"]+)"|';
        if (!$request->headers->has('x-wsse') || 1 !== preg_match($wsseRegex, $request->headers->get('x-wsse'), $matches)) {
            // If we do not have any WSSE headers...
            $event->setResponse($this->responseFactory->createForbidden());

            return;
        }

        $token = new WsseUserToken();
        $token->setDigest($matches[2])
            ->setNonce($matches[3])
            ->setCreated($matches[4])
            ->setUser($matches[1]);

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);
        } catch (AuthenticationException $e) {
            $event->setResponse($this->responseFactory->createUnauthorized());
        }
    }
}
