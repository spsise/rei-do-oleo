<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponseTrait
{
    /**
     * Success response
     */
    protected function successResponse(
        string $message = 'Success',
        mixed $data = null,
        int $code = Response::HTTP_OK
    ): JsonResponse {
        $response = [
            'status' => 'success',
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (config('api.response.include_timestamp', true)) {
            $response['timestamp'] = now()->toISOString();
        }

        if (config('api.response.include_version', true)) {
            $response['version'] = config('api.version');
        }

        return response()->json($response, $code);
    }

    /**
     * Error response
     */
    protected function errorResponse(
        string $message = 'Error',
        mixed $errors = null,
        int $code = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        $response = [
            'status' => 'error',
            'message' => $message,
            'code' => $code,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if (config('api.response.include_timestamp', true)) {
            $response['timestamp'] = now()->toISOString();
        }

        if (config('api.response.include_version', true)) {
            $response['version'] = config('api.version');
        }

        return response()->json($response, $code);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse(
        mixed $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse(
            $message,
            $errors,
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(
        string $message = 'Resource not found'
    ): JsonResponse {
        return $this->errorResponse(
            $message,
            null,
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return $this->errorResponse(
            $message,
            null,
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(
        string $message = 'Forbidden'
    ): JsonResponse {
        return $this->errorResponse(
            $message,
            null,
            Response::HTTP_FORBIDDEN
        );
    }

    /**
     * Internal server error response
     */
    protected function serverErrorResponse(
        string $message = 'Internal server error'
    ): JsonResponse {
        return $this->errorResponse(
            $message,
            null,
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Created response
     */
    protected function createdResponse(
        string $message = 'Resource created successfully',
        mixed $data = null
    ): JsonResponse {
        return $this->successResponse(
            $message,
            $data,
            Response::HTTP_CREATED
        );
    }

    /**
     * No content response
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
