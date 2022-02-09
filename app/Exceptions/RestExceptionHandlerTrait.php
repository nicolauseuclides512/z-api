<?php

namespace App\Exceptions;


use App\Cores\Jsonable;
use BadMethodCallException;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait RestExceptionHandlerTrait
{

    use Jsonable;

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param Request $request
     * @param Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForException(Request $request, Exception $e)
    {
        switch (true) {
            case $this->isModelNotFoundException($e):
                $res = $this->modelNotFound();
                break;

            case $this->isNotFoundHttpException($e):
                $res = $this->httpNotFound($e);
                break;

            case $this->isBadMethodCallException($e):
                $res = $this->badRequest();
                break;

            case $this->isAppException($e):
                $res = $this->appException($e);
                break;

            case $this->isQueryException($e):
                $res = $this->queryError($e->getMessage());
                break;

            case $this->isGuzzleException($e):
                $res = $this->internalError($e->getMessage());
                break;

            case $this->isRequestException($e):
                $res = $res = $this->error($e->getMessage());

                break;


            default:
                $res = $this->error($e->getMessage());
        }

        Log::error('Exception File: '
            . $e->getFile() . ', Line: '
            . $e->getLine() . ", Msg: "
            . $e->getMessage());

        return $res;
    }

    protected function badRequest($message = 'Bad request', $statusCode = Response::HTTP_BAD_REQUEST)
    {
        return $this->jsonResponse([
            'code' => $statusCode,
            'message' => $message
        ], $statusCode);
    }

    protected function queryError($message = 'Query Error', $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return $this->jsonResponse([
            'code' => $statusCode,
            'message' => $message
        ], $statusCode);
    }

    protected function internalError($msg = 'Internal Error', $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return $this->jsonResponse([
            'code' => $code,
            'message' => $msg
        ], $code);
    }

    protected function error($msg, $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return $this->jsonResponse([
            'code' => $code,
            'message' => $msg
        ], $code);
    }

    protected function modelNotFound($message = 'Record not found', $statusCode = Response::HTTP_NOT_FOUND)
    {
        return $this->jsonResponse([
            'code' => Response::HTTP_NOT_FOUND,
            'message' => $message
        ], $statusCode);

    }

    protected function httpNotFound(Exception $e)
    {
        return $this->jsonResponse([
            'code' => Response::HTTP_NOT_FOUND,
            'message' => 'Http Not Found'
        ], 404);

    }


    protected function appException($e)
    {
        return $this->jsonResponse([
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'data' => $e->getCause()
        ], $e->getCode() ? $e->getCode() : 500);

    }

    protected function swiftTransportException(Exception $e)
    {
        return $this->jsonResponse([
            'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'message' => $e->getMessage(),
        ], $e->getCode() ? $e->getCode() : 500);

    }

    /**
     * Returns json response.
     *
     * @param array|null $payload
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse(array $payload = null,
                                    $statusCode = Response::HTTP_NOT_FOUND)
    {
        $payload = $payload ?: [];

        return response()->json($payload, $statusCode);
    }

    /**
     * Determines if the given exception is an Eloquent model not found.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isModelNotFoundException(Exception $e)
    {
        return $e instanceof ModelNotFoundException;
    }

    protected function isNotFoundHttpException(Exception $e)
    {
        return $e instanceof NotFoundHttpException;
    }

    protected function isBadMethodCallException(Exception $e)
    {
        return $e instanceof BadMethodCallException;
    }

    protected function isAppException(Exception $e)
    {
        return $e instanceof AppException;
    }

    protected function isQueryException(Exception $e)
    {
        return $e instanceof QueryException;
    }

    protected function isGuzzleException(Exception $e)
    {
        return $e instanceof GuzzleException;
    }

    protected function isRequestException(Exception $e)
    {
        return $e instanceof RequestException;
    }

}