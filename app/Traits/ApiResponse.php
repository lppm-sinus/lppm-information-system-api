<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($data, $message, $code)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    protected function authSuccessResponse($data, $message, $token, $code)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'token' => $token
        ], $code);
    }

    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    protected function paginatedResponse($collection, $message, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $collection->items(),
            'meta' => [
                'total' => $collection->total(),
                'per_page' => $collection->perPage(),
                'current_page' => $collection->currentPage(),
                'last_page' => $collection->lastPage(),
            ]
        ], );
    }

    protected function formatValidationErrors($validator, $code = 422)
    {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], $code);
    }

    protected function importValidationErrorsResponse($failures, $code = 422)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => collect($failures)->map(function ($failure) {
                return [
                    'row' => $failure->row(),
                    'field' => $failure->attribute(),
                    'error' => $failure->errors()[0],
                ];
            })
        ], $code);
    }
}


