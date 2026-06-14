<?php

namespace App\Http\Controllers;

use App\Transfer\User;

abstract class Controller
{
    // przechowuje usera w sesji (uzywane w Login i Register)
    protected function loginAs(User $user): void {
        session(['user' => serialize($user)]);
    }

    // wczytuje usera z sesji (lub null dla gosci)
    protected function currentUser(): ?User {
        return session('user') ? unserialize(session('user')) : null;
    }
}
