<?php

namespace Happyr\ApiBundle\Security\Authentication\Provider;

use Happyr\ApiBundle\Security\Authentication\Token\WsseUserToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Debug provider is always open. No security here.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class DebugProvider implements AuthenticationProviderInterface
{
    /**
     * Dummy constructor.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $authenticatedToken = new WsseUserToken(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_FETCH', 'ROLE_STORE']);

        return $authenticatedToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return true;
    }
}
