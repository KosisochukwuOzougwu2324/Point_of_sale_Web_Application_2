<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\IUserRepository;
use App\Repositories\UserRepository;

class UserService implements IUserService
{
    private IUserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function getAll(): array
    {
        return $this->userRepository->getAll();
    }

    public function getById(int $id): ?User
    {
        return $this->userRepository->getById($id);
    }

    public function getByEmail(string $email): ?User
    {
        return $this->userRepository->getByEmail($email);
    }

    public function create(User $user): User
    {
        return $this->userRepository->create($user);
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->userRepository->updateStatus($id, $status);
    }

    public function updateProfile(int $id, string $email, string $username, ?string $phone, ?string $address): void
    {
        $this->userRepository->updateProfile($id, $email, $username, $phone, $address);
    }

    public function resetPassword(string $email, string $hashedPassword): void
    {
        $this->userRepository->resetPassword($email, $hashedPassword);
    }

    public function getDrivers(): array
    {
        return $this->userRepository->getByRole('Driver');
    }
}
