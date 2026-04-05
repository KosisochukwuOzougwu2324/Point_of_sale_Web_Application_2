<?php

namespace App\Middleware;

use App\Config;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    
    public static function generateToken(array $userData): string
    {
        $issuedAt = time();

        $payload = [
            'iss' => Config::JWT_ISSUER,
            'aud' => Config::JWT_ISSUER,
            'iat' => $issuedAt,
            'nbf' => $issuedAt,
            'exp' => $issuedAt + Config::JWT_EXPIRY,
            'data' => $userData
        ];

        return JWT::encode($payload, Config::JWT_SECRET, 'HS256');
    }

   
    public static function validateToken(): ?object
    {
        $token = self::getBearerToken();

        if (!$token) {
            return null;
        }

        try {
            $decoded = JWT::decode($token, new Key(Config::JWT_SECRET, 'HS256'));
            return $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }

   
    public static function requireAuth(): object
    {
        $user = self::validateToken();

        if (!$user) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        return $user;
    }

    public static function requireRole(string $role): object
    {
        $user = self::requireAuth();

        if ($user->role !== $role) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }

        return $user;
    }

    
    public static function requireAnyRole(array $roles): object
    {
        $user = self::requireAuth();

        if (!in_array($user->role, $roles)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }

        return $user;
    }

    private static function getBearerToken(): ?string
    {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        if ($headers && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
