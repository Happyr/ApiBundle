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
     * @var array
     */
    private $debugRoles;

    /**
     * @param array $debugRoles
     *
     * @return DebugProvider
     */
    public function setDebugRoles($debugRoles)
    {
        $this->debugRoles = $debugRoles;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $authenticatedToken = new WsseUserToken($this->debugRoles);

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
