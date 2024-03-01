<?php

namespace ParasolCRM\Services\CRM\Traits;

use Illuminate\Http\JsonResponse;

trait Response
{
    public function response(array $data = [], string $message = '', int $code = 404): JsonResponse
    {
        $response = ['message' => $message];
        if (count($data)) {
            $response['data'] = $data;
        }
        return response()->json($response, $code);
    }

    public function responseSuccess(string $message = 'Success', int $code = 200): JsonResponse
    {
        return $this->response([], $message, $code);
    }

    public function responseData(array $data = [], string $message = '', int $code = 200): JsonResponse
    {
        return $this->response($data, $message, $code);
    }

    public function responseError(string $message = 'Error', int $code = 404): JsonResponse
    {
        return $this->response([], $message, $code);
    }
}
