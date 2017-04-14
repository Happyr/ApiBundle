<?php

namespace Happyr\ApiBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Class WsseProvider.
 *
 * @author Toby Ryuk
 *
 * Sets up a custom token for Wsse
 */
class WsseUserToken extends AbstractToken
{
    /**
     * @var
     */
    public $created;

    /**
     * @var string
     */
    public $digest;

    /**
     * @var string
     */
    public $nonce;

    /**
     * WsseUserToken constructor.
     *
     * @param array $roles
     */
    public function __construct(array $roles = array())
    {
        parent::__construct($roles);

        $this->setAuthenticated(count($roles) > 0);
    }

    /**
     * @return string
     */
    public function getCredentials()
    {
        return '';
    }
}
