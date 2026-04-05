<?php

namespace App\Repositories;

use App\Models\User;
use App\Framework\Repository;

class UserRepository extends Repository implements IUserRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAll(): array
    {
        $sql = 'SELECT id, username, email, role, status, phone, address FROM users';
        $result = $this->getConnection()->query($sql);
        return $result->fetchAll(\PDO::FETCH_CLASS, User::class);
    }

    public function getById(int $id): ?User
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT id, username, email, password, role, status, phone, address FROM users WHERE id = :id'
        );
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetchObject(User::class);
        return $user === false ? null : $user;
    }

    public function getByEmail(string $email): ?User
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT id, username, email, password, role, status, phone, address FROM users WHERE email = :email'
        );
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetchObject(User::class);
        return $user === false ? null : $user;
    }

    public function create(User $user): User
    {
        $existing = $this->getByEmail($user->email);
        if ($existing) {
            throw new \Exception('Email already exists');
        }

        $stmt = $this->getConnection()->prepare(
            'INSERT INTO users (username, email, password, role, status, phone, address) 
             VALUES (:username, :email, :password, :role, :status, :phone, :address)'
        );

        $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);

        $stmt->bindParam(':username', $user->username);
        $stmt->bindParam(':email', $user->email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $user->role);
        $stmt->bindParam(':status', $user->status);
        $stmt->bindParam(':phone', $user->phone);
        $stmt->bindParam(':address', $user->address);
        $stmt->execute();

        $user->id = (int) $this->getConnection()->lastInsertId();
        $user->password = ''; // Don't return the password
        return $user;
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->getConnection()->prepare('UPDATE users SET status = :status WHERE id = :id');
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
    }

    public function updateProfile(int $id, string $email, string $username, ?string $phone, ?string $address): void
    {
        $existing = $this->getByEmail($email);
        if ($existing && $existing->id !== $id) {
            throw new \Exception('Email is already taken');
        }

        $stmt = $this->getConnection()->prepare(
            'UPDATE users SET email = :email, username = :username, phone = :phone, address = :address WHERE id = :id'
        );
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public function resetPassword(string $email, string $hashedPassword): void
    {
        $stmt = $this->getConnection()->prepare('UPDATE users SET password = :password WHERE email = :email');
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();
    }

    public function getByRole(string $role): array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT id, username, email, role, status FROM users WHERE role = :role AND status = "Active"'
        );
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_CLASS, User::class);
    }
}
