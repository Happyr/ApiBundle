<?php

namespace Happyr\ApiBundle\EventListener;

use Fervo\ValidatedMessage\ValidationFailedException;
use Happyr\ApiBundle\Exception\ApiException;
use Happyr\ApiBundle\Model\ApiError;
use Happyr\ApiBundle\Model\ApiResponse;
use Happyr\ApiBundle\Service\ErrorHandler;
use Psr\Log\LoggerInterface;
use Happyr\ApiBundle\ViewHandler\ViewHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author Tobias Nyholm
 */
class ExceptionListener
{
    /**
     * @var ErrorHandler errorHandler
     */
    private $errorHandler;

    /**
     * @var ViewHandler viewHandler
     */
    private $viewHandler;

    /**
     * @var LoggerInterface logger
     */
    private $logger;

    /**
     * @var string
     */
    private $pathPrefix;

    /**
     * @param ErrorHandler         $errorHandler
     * @param ViewHandler          $viewHandler
     * @param string               $pathPrefix
     * @param LoggerInterface|null $logger
     */
    public function __construct(ErrorHandler $errorHandler, ViewHandler $viewHandler, $pathPrefix = '/', LoggerInterface $logger = null)
    {
        $this->errorHandler = $errorHandler;
        $this->viewHandler = $viewHandler;
        $this->logger = $logger;
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
        if (!preg_match('|^'.$this->pathPrefix.'.*|sim', $event->getRequest()->getPathInfo())) {
            return;
        }

        $exception = $event->getException();
        if ($exception instanceof ApiException) {
            $logLevel = 'debug';
            $error = $exception->getApiError();
        } elseif ($exception instanceof AccessDeniedException) {
            $logLevel = 'warning';
            $error = new ApiError(6);
        } elseif ($exception instanceof AuthenticationException) {
            $logLevel = 'warning';
            $error = new ApiError(5);
        } elseif ($exception instanceof BadRequestHttpException) {
            $logLevel = 'warning';
            $error = new ApiError(2);
            $error->setMessage($exception->getMessage());
        } elseif ($exception instanceof MethodNotAllowedException || $exception instanceof MethodNotAllowedHttpException) {
            $logLevel = 'notice';
            $error = new ApiError(3);
        } elseif ($exception instanceof NotFoundHttpException) {
            $logLevel = 'notice';
            $error = new ApiError(4);
        } elseif ($exception instanceof ValidationFailedException) {
            $logLevel = 'notice';
            $error = new ApiError(7);
            $message = '';

            /** @var ConstraintViolationInterface $violation */
            foreach ($exception->getViolations() as $violation) {
                $message .= sprintf('%s: %s, ', $violation->getPropertyPath(), $violation->getMessage());
            }

            $error->setMessage($message);
        } else {
            $logLevel = 'critical';
            $error = new ApiError(1);
        }

        $this->log($logLevel, $exception);
        $this->errorHandler->prepareErrorResponse($error);

        try {
            $response = $this->viewHandler->handle(new ApiResponse($error), $error->getHttpStatus());
        } catch (\Exception $e) {
            $response = new Response('Could not handle exception view.', 500);
        }

        $event->setResponse($response);
    }

    /**
     * @param string     $level
     * @param \Exception $exception
     */
    protected function log($level, \Exception $exception)
    {
        if ($this->logger === null) {
            return;
        }

        $message = sprintf(
            'Uncaught PHP Exception %s: "%s" at %s line %s',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        $this->logger->log($level, $message, array('exception' => $exception));
    }
}
