<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Services\IUserService;
use App\Services\UserService;

class UserController extends Controller
{
    private IUserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }


    public function getAll()
    {
        AuthMiddleware::requireRole('Admin');

        $users = $this->userService->getAll();
        return $this->sendSuccessResponse(['users' => $users]);
    }

    
    public function getById($vars = [])
    {
        AuthMiddleware::requireRole('Admin');

        $id = (int)($vars['id'] ?? 0);
        $user = $this->userService->getById($id);

        if (!$user) {
            return $this->sendErrorResponse('User not found', 404);
        }

        return $this->sendSuccessResponse(['user' => $user]);
    }

   
    public function create()
    {
        AuthMiddleware::requireRole('Admin');

        $data = $this->getRequestBody();

        if (!$data || empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['role'])) {
            return $this->sendErrorResponse('Username, email, password and role are required', 400);
        }

        try {
            $user = new User();
            $user->username = trim($data['username']);
            $user->email = trim($data['email']);
            $user->password = $data['password'];
            $user->role = $data['role'];
            $user->status = 'Active';
            $user->phone = $data['phone'] ?? null;
            $user->address = $data['address'] ?? null;

            $createdUser = $this->userService->create($user);

            return $this->sendSuccessResponse([
                'message' => 'User successfully created',
                'user' => $createdUser
            ], 201);

        } catch (\Exception $e) {
            return $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    
    public function updateStatus($vars = [])
    {
        AuthMiddleware::requireRole('Admin');

        $id = (int)($vars['id'] ?? 0);
        $data = $this->getRequestBody();

        if (!$data || empty($data['status'])) {
            return $this->sendErrorResponse('Status is required', 400);
        }

        $user = $this->userService->getById($id);
        if (!$user) {
            return $this->sendErrorResponse('User not found', 404);
        }

        $this->userService->updateStatus($id, $data['status']);

        $action = strcasecmp($data['status'], 'Blocked') === 0 ? 'blocked' : 'activated';
        return $this->sendSuccessResponse(['message' => "User successfully $action"]);
    }

    public function updateProfile($vars = [])
    {
        $authUser = AuthMiddleware::requireAuth();

        $id = (int)($vars['id'] ?? 0);

        if ($authUser->id !== $id && $authUser->role !== 'Admin') {
            return $this->sendErrorResponse('Access denied', 403);
        }

        $data = $this->getRequestBody();

        if (!$data || empty($data['email']) || empty($data['username'])) {
            return $this->sendErrorResponse('Email and username are required', 400);
        }

        try {
            $this->userService->updateProfile(
                $id,
                trim($data['email']),
                trim($data['username']),
                $data['phone'] ?? null,
                $data['address'] ?? null
            );

            return $this->sendSuccessResponse(['message' => 'Profile successfully updated']);
        } catch (\Exception $e) {
            return $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

   
    public function resetPassword()
    {
        $data = $this->getRequestBody();

        if (!$data || empty($data['email']) || empty($data['password'])) {
            return $this->sendErrorResponse('Email and new password are required', 400);
        }

        if (strlen($data['password']) < 6) {
            return $this->sendErrorResponse('Password must be at least 6 characters', 400);
        }

        $user = $this->userService->getByEmail($data['email']);
        if (!$user) {
            return $this->sendErrorResponse('User not found', 404);
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $this->userService->resetPassword($data['email'], $hashedPassword);

        return $this->sendSuccessResponse(['message' => 'Password successfully updated']);
    }

    
    public function getDrivers()
    {
        AuthMiddleware::requireRole('Admin');

        $drivers = $this->userService->getDrivers();
        return $this->sendSuccessResponse(['drivers' => $drivers]);
    }
}
