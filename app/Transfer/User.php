<?php

namespace App\Transfer;

class User {
    public function __construct(
    public string $email,
    public string $role,
    public int $id,
    public string $nickname
    ){}

    public function isAdmin(): bool {
        return $this->role === 'admin';
    }
}
