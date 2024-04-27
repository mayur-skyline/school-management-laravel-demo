<?php

namespace App\Http\Middleware;

use Closure;

class authLogin {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $isLoggedIn = isLoggedIn(); // commonHelper function
        $userLevel = myLevel();

        if ($isLoggedIn == 1) {
            return $next($request);
        } elseif ($isLoggedIn == 2) {
            return redirect('new-login-view');
        } elseif ($isLoggedIn == 4) {
            return redirect('new-check-step1');
        } elseif ($isLoggedIn == 3) {
            if ($userLevel == 1) {
                return redirect('new-login-view');
            } else {
                return redirect('new-staff-login-view');
            }
        } else {
            return redirect('new-check-step1');
        }
    }

}
