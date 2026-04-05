<?php

namespace App\Framework;

class Controller
{
    protected function sendSuccessResponse($data = [], $code = 200)
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    protected function sendErrorResponse($message, $code = 500)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode(['error' => $message], JSON_PRETTY_PRINT);
    }

    protected function getRequestBody(): ?array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }

    protected function mapPostDataToClass(string $className): ?object
    {
        $data = $this->getRequestBody();
        if (!$data) return null;

        $instance = new $className();
        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        return $instance;
    }
}
