<?php

namespace Happyr\ApiBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Sets up a custom token for Wsse.
 *
 * @author Toby Ryuk
 */
class WsseUserToken extends AbstractToken
{
    /**
     * @var string
     */
    private $created;

    /**
     * @var string
     */
    private $digest;

    /**
     * @var string
     */
    private $nonce;

    /**
     * WsseUserToken constructor.
     *
     * @param array $roles
     */
    public function __construct(array $roles = [])
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

    /**
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param string $created
     *
     * @return WsseUserToken
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return string
     */
    public function getDigest()
    {
        return $this->digest;
    }

    /**
     * @param string $digest
     *
     * @return WsseUserToken
     */
    public function setDigest($digest)
    {
        $this->digest = $digest;

        return $this;
    }

    /**
     * @return string
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @param string $nonce
     *
     * @return WsseUserToken
     */
    public function setNonce($nonce)
    {
        $this->nonce = $nonce;

        return $this;
    }
}
