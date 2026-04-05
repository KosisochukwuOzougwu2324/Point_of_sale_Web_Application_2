<?php

namespace App\Models;

class User implements \JsonSerializable
{
    public ?int $id = null;
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'Customer';
    public string $status = 'Active';
    public ?string $phone = null;
    public ?string $address = null;

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'phone' => $this->phone,
            'address' => $this->address
        ];
    }
}
