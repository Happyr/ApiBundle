<?php

namespace Happyr\ApiBundle\Security\Firewall;

use Happyr\ApiBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Happyr\ApiBundle\Security\Authentication\Token\WsseUserToken;

/**
 * Listens for incoming events and checks if they have x-wsse in the header. If not ignore, otherwise, sets up a
 * token and sends it of to validation. If validation passes, stores the token in the cache. If it fails, throw
 * an exception
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class WsseListener implements ListenerInterface
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
        $request = $event->getRequest();

        $wsseRegex = '|UsernameToken Username="([^"]+)", PasswordDigest="([^"]+)", Nonce="([a-zA-Z0-9+/]+={0,2})", Created="([^"]+)"|';
        if (!$request->headers->has('x-wsse') || 1 !== preg_match($wsseRegex, $request->headers->get('x-wsse'), $matches)) {
            // If we do not have any WSSE headers...
            $response = new JsonResponse([
                'error' => [
                    'code' => BaseController::CODE_FORBIDDEN,
                    'http_code' => Response::HTTP_FORBIDDEN,
                    'message' => 'Forbidden',
                ],
            ], Response::HTTP_FORBIDDEN);

            $event->setResponse($response);

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
            $response = new JsonResponse([
                'error' => [
                    'code' => BaseController::CODE_UNAUTHORIZED,
                    'http_code' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Unauthorized',
                ],
            ], Response::HTTP_UNAUTHORIZED);

            $event->setResponse($response);
        }
    }
}
