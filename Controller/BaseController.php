<?php

namespace Happyr\ApiBundle\Controller\Api;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class BaseController extends Controller
{
    private $statusCode = 200;

    const CODE_WRONG_ARGS = 'GEN-ARGUMENTS';
    const CODE_NOT_FOUND = 'GEN-NOTFOUND';
    const CODE_INTERNAL_ERROR = 'GEN-SERVERERROR';
    const CODE_UNAUTHORIZED = 'GEN-UNAUTHORIZED';
    const CODE_FORBIDDEN = 'GEN-FORBIDDEN';

    /**
     * Getter for statusCode.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode.
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param mixed $item
     * @param $callback
     *
     * @return JsonResponse
     */
    protected function respondWithItem($item, $callback)
    {
        $resource = new Item($item, $callback);
        $rootScope = $this->get('app.fractal')->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * @param mixed $collection
     * @param $callback
     *
     * @return JsonResponse
     */
    protected function respondWithCollection($collection, $callback)
    {
        $resource = new Collection($collection, $callback);
        $rootScope = $this->get('app.fractal')->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * @param string $message
     * @param int    $errorCode
     *
     * @return JsonResponse
     */
    protected function respondWithArray(array $array, array $headers = [])
    {
        return new JsonResponse($array, $this->statusCode, $headers);
    }

    /**
     * @param string $message
     * @param int    $errorCode
     *
     * @return JsonResponse
     */
    protected function respondWithError($message, $errorCode)
    {
        if ($this->statusCode === 200) {
            trigger_error(
                'You better have a really good reason for erroring on a 200...',
                E_USER_WARNING
            );
        }

        return $this->respondWithArray([
            'error' => [
                'code' => $errorCode,
                'http_code' => $this->statusCode,
                'message' => $message,
            ],
        ]);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @return JsonResponse
     */
    public function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)
            ->respondWithError($message, self::CODE_FORBIDDEN);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @return JsonResponse
     */
    public function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(500)
            ->respondWithError($message, self::CODE_INTERNAL_ERROR);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @return JsonResponse
     */
    public function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)
            ->respondWithError($message, self::CODE_NOT_FOUND);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @return JsonResponse
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(401)
            ->respondWithError($message, self::CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @return JsonResponse
     */
    public function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)
            ->respondWithError($message, self::CODE_WRONG_ARGS);
    }
}
