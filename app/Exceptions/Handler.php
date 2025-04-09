<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        // Add exceptions that shouldn't be reported
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // HTTP not found
        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
            $message = Response::$statusTexts[$code];

            return $this->errorResponse($message, $code);
        }

        // Model instance not found
        if ($exception instanceof ModelNotFoundException) {
            $model = $exception->getModel();
            $modelName = $model ? class_basename($model) : 'resource';
        
            return response()->json([
                'error' => "Does not exist any instance of {$modelName} with the given id",
                'code' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
        
        // Validation exception
        if ($exception instanceof ValidationException) {
            $errors = $exception->validator->errors()->getMessages();
            
            return $this->errorResponse($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Access forbidden
        if ($exception instanceof AuthorizationException) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        // Unauthorized access
        if ($exception instanceof AuthenticationException) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        // If you are running in development environment
        if (env('APP_DEBUG', false)) {
            return parent::render($request, $exception);
        }

        return $this->errorResponse('Unexpected error. Try later', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}