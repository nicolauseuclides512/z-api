<?php

namespace App\Exceptions\Traits;

use App\Exceptions\AppException;
use BadMethodCallException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait RestHandlerTrait
{

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param Request $request
     * @param Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function exceptionResponse(Request $request,
                                         Exception $e)
    {
        switch (true) {
            case $e instanceof BadRequestHttpException:
                $res = $this->badRequest($e);
                break;

            case $e instanceof NotFoundHttpException:
                $res = $this->notFoundHttpException($e);
                break;

            case $e instanceof ModelNotFoundException:
                $res = $this->modelNotFoundException($e);
                break;

            case $e instanceof BadMethodCallException:
                $res = $this->badMethodCallException($e);
                break;

            case $e instanceof \Swift_TransportException:
                $res = $this->swiftTransportException($e);
                break;

            case $e instanceof AppException:
                $res = $this->appException($e);
                break;

            case $e instanceof ValidationException:
                $res = $this->validationException($e);
                break;

//            case $e instanceof EntryNotFoundException:
//                $res = $this->entryNotFoundException($e);
//                break;

            default:
                Log::error("
                Type: ValidationException 
                    File: {$e->getFile()}, 
                    Line: {$e->getLine()}, 
                    Msg: " . $e->getMessage());
                $res = $this->jsonResponse([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ], 500);
        }

        return $res;
    }

    //BadRequestHttpException
    protected function badRequest(BadRequestHttpException $e, $statusCode = Response::HTTP_BAD_REQUEST)
    {
        Log::error("
        Type: BadRequestHttpException 
        File: {$e->getFile()}, 
        Line: {$e->getLine()}, 
        Msg: {$e->getMessage()}");

        return $this->jsonResponse([
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => $e->getMessage()
        ], $statusCode);
    }

    //ModelNotFoundException
    protected function modelNotFound(ModelNotFoundException $e, $statusCode = Response::HTTP_BAD_REQUEST)
    {
        Log::error("
        Type: ModelNotFoundException 
        File: {$e->getFile()}, 
        Line: {$e->getLine()}, 
        Msg: {$e->getMessage()}");

        return $this->jsonResponse([
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => $e->getMessage()
        ], $statusCode);
    }

    //NotFoundHttpException
    protected function notFoundHttpException(NotFoundHttpException $e, $statusCode = Response::HTTP_BAD_REQUEST)
    {
        Log::error("
        Type: NotFoundHttpException 
        File: {$e->getFile()}, 
        Line: {$e->getLine()}, 
        Msg: {$e->getMessage()}");

        return $this->jsonResponse([
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => $e->getMessage()
        ], $statusCode);
    }


    //ModelNotFoundException
    protected function modelNotFoundException(ModelNotFoundException $e, $statusCode = Response::HTTP_BAD_REQUEST)
    {
        Log::error("
        Type: ModelNotFoundHttpException 
        File: {$e->getFile()}, 
        Line: {$e->getLine()}, 
        Msg: {$e->getMessage()}");

        return $this->jsonResponse([
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => $e->getMessage()
        ], $statusCode);
    }


    //BadMethodCallException
    protected function badMethodCallException(BadMethodCallException $e)
    {
        Log::error("
        Type: BadMethodCallException 
        File: {$e->getFile()}, 
        Line: {$e->getLine()}, 
        Msg: {$e->getMessage()}");

        return $this->jsonResponse([
            'code' => Response::HTTP_NOT_FOUND,
            'message' => $e->getMessage()
        ], Response::HTTP_BAD_REQUEST);

    }

    //AppException
    protected function appException(AppException $e)
    {

        Log::error("
        Type: AppException 
        File: {$e->getFile()}, 
        Line: {$e->getLine()}, 
        Msg: {$e->getMessage()}
        Cause: " . json_encode($e->getCause())
        );

        return $this->jsonResponse([
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'data' => $e->getCause()
        ], $e->getCode() ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR);

    }

    //Swift_TransportException
    protected function swiftTransportException(\Swift_TransportException $e)
    {
        Log::error("
        Type: Exception 
        File: {$e->getFile()},
        Line: {$e->getLine()},
        Msg: {$e->getMessage()}");

        return $this->jsonResponse([
            'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'message' => $e->getMessage(),
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    //ValidationException
    protected function validationException(ValidationException $e)
    {
        Log::error("
        Type: ValidationException 
        File: {$e->getFile()},
        Line: {$e->getLine()},
        Msg: " . $e->validator->errors()->toJson());

        return $this->jsonResponse([
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => $e->getMessage(),
            'data' => $e->validator->errors()->all()
        ], Response::HTTP_BAD_REQUEST);

    }

//    //ValidationException
//    protected function entryNotFoundException(EntryNotFoundException $e)
//    {
//        Log::error("
//        Type: EntryNotFoundException
//        File: {$e->getFile()},
//        Line: {$e->getLine()}");
//
//        return $this->jsonResponse([
//            'code' => Response::HTTP_BAD_REQUEST,
//            'message' => $e->getMessage(),
//        ], Response::HTTP_BAD_REQUEST);
//
//    }

    /**
     * Returns json response.
     *
     * @param array|null $payload
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse(array $payload = null, $statusCode = Response::HTTP_NOT_FOUND)
    {
        $payload = $payload ?: [];

        return response()->json($payload, $statusCode);
    }
}
