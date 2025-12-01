<?php

use App\Support\ApiResponse;

if (!function_exists('api_success')) {
    /**
     * Return a success JSON response.
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    function api_success($data = null, ?string $message = null, int $status = 200)
    {
        return ApiResponse::success($data, $message, $status);
    }
}

if (!function_exists('api_error')) {
    /**
     * Return an error JSON response.
     *
     * @param string|null $message
     * @param int $status
     * @param mixed $errors
     * @return \Illuminate\Http\JsonResponse
     */
    function api_error(?string $message = null, int $status = 400, $errors = null)
    {
        return ApiResponse::error($message, $status, $errors);
    }
}
