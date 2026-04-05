<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Services\IUserService;
use App\Services\UserService;

class AuthController extends Controller
{
    private IUserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

   
    public function login()
    {
        $data = $this->getRequestBody();

        if (!$data || empty($data['email']) || empty($data['password'])) {
            return $this->sendErrorResponse('Email and password are required', 400);
        }

        $user = $this->userService->getByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user->password)) {
            return $this->sendErrorResponse('Invalid email or password', 401);
        }

        if (strcasecmp($user->status, 'Active') !== 0) {
            return $this->sendErrorResponse('Your account is deactivated, kindly contact Admin', 403);
        }

        $token = AuthMiddleware::generateToken([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role
        ]);

        return $this->sendSuccessResponse([
            'token' => $token,
            'user' => $user
        ]);
    }

   
    public function register()
    {
        $data = $this->getRequestBody();

        if (!$data || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return $this->sendErrorResponse('Username, email and password are required', 400);
        }

        if (strlen($data['password']) < 6) {
            return $this->sendErrorResponse('Password must be at least 6 characters', 400);
        }

        try {
            $user = new User();
            $user->username = trim($data['username']);
            $user->email = trim($data['email']);
            $user->password = $data['password'];
            $user->role = 'Customer';
            $user->status = 'Active';
            $user->phone = $data['phone'] ?? null;
            $user->address = $data['address'] ?? null;

            $createdUser = $this->userService->create($user);

            $token = AuthMiddleware::generateToken([
                'id' => $createdUser->id,
                'username' => $createdUser->username,
                'email' => $createdUser->email,
                'role' => $createdUser->role
            ]);

            return $this->sendSuccessResponse([
                'token' => $token,
                'user' => $createdUser
            ], 201);

        } catch (\Exception $e) {
            return $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

   
    public function me()
    {
        $authUser = AuthMiddleware::requireAuth();

        $user = $this->userService->getById($authUser->id);

        if (!$user) {
            return $this->sendErrorResponse('User not found', 404);
        }

        return $this->sendSuccessResponse(['user' => $user]);
    }
}
