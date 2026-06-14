<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Transfer\User;

class CheckAdmin
{
    // sprawdza czy zalogowany user ma role 'admin'
    // jezeli nie -> przekierowanie do home z bledem
    public function handle(Request $request, Closure $next) {
        $user = session('user') ? unserialize(session('user')) : null;

        if (!($user instanceof User && $user->isAdmin())) {
            return redirect()->route('home')
                ->with('infos', ['Nie masz uprawnień do panelu administratora.']);
        }

        return $next($request);
    }
}
