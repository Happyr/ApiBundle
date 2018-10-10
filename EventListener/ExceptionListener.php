<?php

namespace Happyr\ApiBundle\EventListener;

use Fervo\ValidatedMessage\ValidationFailedException as FervoValaidationFailed;
use Happyr\ApiBundle\Service\ResponseFactory;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Messenger\Exception\ValidationFailedException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ExceptionListener
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var string
     */
    private $pathPrefix;

    /**
     * @param ResponseFactory $responseFactory
     * @param string          $pathPrefix
     */
    public function __construct(ResponseFactory $responseFactory, $pathPrefix)
    {
        $this->responseFactory = $responseFactory;
        $this->pathPrefix = $pathPrefix;
    }

    /**
     * Make sure we print a nice error message to the user when we encounter an exception.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // Make sure to match uri before we start to catch exceptions
        if (!preg_match('|^' . $this->pathPrefix . '.*|sim', $event->getRequest()->getPathInfo())) {
            return;
        }

        $exception = $event->getException();
        if ($exception instanceof AccessDeniedException || $exception instanceof AccessDeniedHttpException) {
            $response = $this->responseFactory->createForbidden();
        } elseif ($exception instanceof AuthenticationException) {
            $response = $this->responseFactory->createUnauthorized();
        } elseif ($exception instanceof BadRequestHttpException) {
            $response = $this->responseFactory->createWrongArgs();
        } elseif ($exception instanceof MethodNotAllowedException || $exception instanceof MethodNotAllowedHttpException) {
            $response = $this->responseFactory->createWithError('Method not allowed', 405, 'GEN-METHOD');
        } elseif ($exception instanceof NotFoundHttpException) {
            $response = $this->responseFactory->createNotFound();
        } elseif ($exception instanceof FervoValaidationFailed || $exception instanceof ValidationFailedException) {
            $response = $this->responseFactory->createValidationFailed($exception->getViolations());
        } else {
            $response = $this->responseFactory->createInternalError();
        }

        $event->setResponse($response);
    }
}
