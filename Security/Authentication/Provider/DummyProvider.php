<?php

namespace Happyr\ApiBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Dummy provider that should be used when wsse is not active.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class DummyProvider implements AuthenticationProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        throw new AuthenticationException('Dummy provider.');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return false;
    }
}
