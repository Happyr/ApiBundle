<?php

namespace Happyr\ApiBundle\Security\Authentication\Provider;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Happyr\ApiBundle\Security\Authentication\Token\WsseUserToken;

/**
 * The authentication provider will do the verification of the WsseUserToken. Namely, the provider will verify the
 * Created header value is valid within the specified lifetime, the Nonce header value is unique within the
 * specified lifetime, and the PasswordDigest header value matches with the user's password.
 *
 * @author Toby Ryuk
 */
class WsseProvider implements AuthenticationProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheService;

    /**
     * @var int
     */
    private $lifetime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * WsseProvider constructor.
     *
     * @param UserProviderInterface  $userProvider
     * @param CacheItemPoolInterface $cacheService
     * @param $lifetime
     */
    public function __construct(UserProviderInterface $userProvider, CacheItemPoolInterface $cacheService, $lifetime)
    {
        $this->userProvider = $userProvider;
        $this->cacheService = $cacheService;
        $this->lifetime = $lifetime;
    }

    /**
     * @param WsseUserToken $token
     *
     * @return WsseUserToken
     */
    public function authenticate(TokenInterface $token)
    {
        $user = $this->getUser($token);

        if ($this->validateDigest($token->getDigest(), $token->getNonce(), $token->getCreated(), $user->getPassword())) {
            $authenticatedToken = new WsseUserToken($user->getRoles());
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The WSSE authentication failed, invalid token.');
    }

    /**
     * This function is specific to Wsse authentication and is only used to help this example.
     *
     * For more information specific to the logic here, see
     * https://github.com/symfony/symfony-docs/pull/3134#issuecomment-27699129
     */
    protected function validateDigest($digest, $nonce, $created, $secret)
    {
        // Check created time is not in the future
        if (strtotime($created) > time()) {
            $this->log('error', 'Digest not valid. Created timestamp was in the future');

            return false;
        }

        // Expire timestamp after time chosen in the configuration
        if (time() - strtotime($created) > $this->lifetime) {
            $this->log('error', 'Digest not valid. Created timestamp has expired', [
                'lifetime' => $this->lifetime,
                'created' => $created,
                'current_time' => time(),
            ]);

            return false;
        }

        $cacheItem = $this->cacheService->getItem(md5($nonce));
        // Validate that the nonce have not been used in it's lifetime
        // if it has, this could be a replay attack
        if ($cacheItem->isHit()) {
            $this->log('error', 'Digest not valid. Nonce already used');

            throw new NonceExpiredException('Previously used nonce detected');
        }

        // If cache item does not exist, create it
        $cacheItem->set(null)->expiresAfter($this->lifetime - (time() - strtotime($created)));
        $this->cacheService->save($cacheItem);

        // Validate Secret
        $expected = base64_encode(sha1(base64_decode($nonce).$created.$secret, true));

        $result = hash_equals($expected, $digest);

        if (!$result) {
            $this->log('error', 'Digest not valid. Wrong data');
        }

        return $result;
    }

    /**
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof WsseUserToken;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return WsseProvider
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    private function log($level, $message, array $context = [])
    {
        if ($this->logger === null) {
            return;
        }
        $this->logger->log($level, $message, $context);
    }

    /**
     * @param TokenInterface $token
     * @return \Symfony\Component\Security\Core\User\UserInterface
     *
     * @throws AuthenticationException
     */
    protected function getUser(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        if (null === $user) {
            throw new AuthenticationException('User not found.');
        }

        return $user;
    }
}
