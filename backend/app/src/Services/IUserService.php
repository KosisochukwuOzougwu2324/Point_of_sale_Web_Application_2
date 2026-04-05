<?php

namespace App\Services;

use App\Models\User;

interface IUserService
{
    public function getAll(): array;
    public function getById(int $id): ?User;
    public function getByEmail(string $email): ?User;
    public function create(User $user): User;
    public function updateStatus(int $id, string $status): void;
    public function updateProfile(int $id, string $email, string $username, ?string $phone, ?string $address): void;
    public function resetPassword(string $email, string $hashedPassword): void;
    public function getDrivers(): array;
}
