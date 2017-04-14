<?php

namespace Happyr\ApiBundle\EventListener;

use Happyr\ApiBundle\Model\ApiError;
use Happyr\ApiBundle\Model\ApiResponse;
use Happyr\ApiBundle\Service\ErrorHandler;
use Happyr\ApiBundle\ViewHandler\ViewHandler;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class WhenApiResponseIsReturnedFromControllerConvertItToHttpFoundationResponse
{
    /**
     * @var ViewHandler
     */
    private $viewHandler;

    /**
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * @param ViewHandler  $viewHandler
     * @param ErrorHandler $errorHandler
     */
    public function __construct(ViewHandler $viewHandler, ErrorHandler $errorHandler)
    {
        $this->viewHandler = $viewHandler;
        $this->errorHandler = $errorHandler;
    }

    /**
     * Renders the template and initializes a new response object with the
     * rendered template content.
     *
     * @param GetResponseForControllerResultEvent $event A GetResponseForControllerResultEvent instance
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $parameters = $event->getControllerResult();

        if ($parameters instanceof ApiResponse) {
            $response = $this->viewHandler->handle($parameters);

            $event->setResponse($response);
        } elseif ($parameters instanceof ApiError) {
            $this->errorHandler->prepareErrorResponse($parameters);
            $statusCode = $parameters->getHttpStatus();
            $response = $this->viewHandler->handle($parameters, $statusCode);

            $event->setResponse($response);
        }
    }
}
