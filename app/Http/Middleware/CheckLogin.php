<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Transfer\User;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     *
     */
    public function handle(Request $request, Closure $next) {

        $user = session('user') ? unserialize(session('user')) : null;

        // jezeli nie ma user-a w session, przekierowuje na login
        // jak w przykladzie Kudlacika zamiast
        // if ( ! (isset($user) && isset($user->email) && isset($user->role)) )
        if (!($user instanceof User && isset($user->email) && isset($user->role))) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
