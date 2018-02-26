<?php

namespace Happyr\ApiBundle\Service;

use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Symfony\Component\HttpFoundation\JsonResponse;
use League\Fractal\Manager;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ResponseFactory
{
    const CODE_WRONG_ARGS = 'GEN-ARGUMENTS';

    const CODE_NOT_FOUND = 'GEN-NOTFOUND';

    const CODE_INTERNAL_ERROR = 'GEN-SERVERERROR';

    const CODE_UNAUTHORIZED = 'GEN-UNAUTHORIZED';

    const CODE_FORBIDDEN = 'GEN-FORBIDDEN';

    /**
     * @var Manager
     */
    private $fractal;

    /** @var  PaginatorInterface */
    private $paginator;

    /** @var  Cursor */
    private $cursor;

    /**
     * @param Manager $fractal
     */
    public function __construct(Manager $fractal)
    {
        $this->fractal = $fractal;
    }

    /**
     * @return Manager
     */
    public function getFractal()
    {
        return $this->fractal;
    }

    /**
     * @param mixed $item
     * @param $callback
     *
     * @return JsonResponse
     */
    public function createWithItem($item, $callback)
    {
        $resource = new Item($item, $callback);
        $rootScope = $this->fractal->createData($resource);

        return $this->createWithArray($rootScope->toArray());
    }

    /**
     * @param mixed $collection
     * @param $callback
     *
     * @return JsonResponse
     */
    public function createWithCollection($collection, $callback)
    {
        $resource = new Collection($collection, $callback);
        if($this->paginator !== null) {
            $resource->setPaginator($this->paginator);
        }
        $rootScope = $this->fractal->createData($resource);

        return $this->createWithArray($rootScope->toArray());
    }

    public function withPaginator(PaginatorInterface $paginator)
    {
        $new = clone $this;
        $new->paginator = $paginator;

        return $new;
    }

    public function withCursor(CursorInterface $cursor)
    {
        $new = clone $this;
        $new->cursor = $cursor;

        return $new;
    }

    /**
     * @param array $array
     * @param int   $statusCode
     * @param array $headers
     *
     * @return JsonResponse
     */
    public function createWithArray(array $array, $statusCode = 200, array $headers = [])
    {
        return new JsonResponse($array, $statusCode, $headers);
    }

    /**
     * @param string $message
     * @param int    $statusCode
     * @param string $errorCode
     *
     * @return JsonResponse
     */
    public function createWithError($message, $statusCode, $errorCode)
    {
        if (200 === $statusCode) {
            trigger_error(
                'You better have a really good reason for erroring on a 200...',
                E_USER_WARNING
            );
        }

        return $this->createWithArray([
            'error' => [
                'code' => $errorCode,
                'http_code' => $statusCode,
                'message' => $message,
            ],
        ], $statusCode);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @return JsonResponse
     */
    public function createForbidden($message = 'Forbidden')
    {
        return $this->createWithError($message, 403, self::CODE_FORBIDDEN);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @return JsonResponse
     */
    public function createInternalError($message = 'Internal Error')
    {
        return $this->createWithError($message, 500, self::CODE_INTERNAL_ERROR);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @return JsonResponse
     */
    public function createNotFound($message = 'Resource Not Found')
    {
        return $this->createWithError($message, 404, self::CODE_NOT_FOUND);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @return JsonResponse
     */
    public function createUnauthorized($message = 'Unauthorized')
    {
        return $this->createWithError($message, 401, self::CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @return JsonResponse
     */
    public function createWrongArgs($message = 'Wrong Arguments')
    {
        return $this->createWithError($message, 400, self::CODE_WRONG_ARGS);
    }
}
