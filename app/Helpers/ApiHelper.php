<?php

namespace App\Helpers;

class ApiHelper
{

    /**
     * default api response format
     *
     * @param  array $data
     * @return array
     */
    public function success(String $message, $data = [], $http_status = 200)
    {
        $res = [
            'status' => true,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($res, $http_status);
    }

    /**
     * default api response format
     *
     * @param  array $data
     * @return array
     */
    public function failed(String $message, $data = [], $http_status = 400)
    {
        $res = [
            'status' => false,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($res, $http_status);
    }
}
