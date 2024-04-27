<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class status2 {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $isLoggedIn = isLoggedIn();
        $userLevel = myLevel();
        if (isset($isLoggedIn) && !empty($isLoggedIn)) {
            if ($isLoggedIn == 4) {
//                return redirect('check-step1');
                return redirect('new-check-step1');
            } else if ($isLoggedIn == 3) {
//                return redirect('login-view');
                return redirect('new-login-view');
            } else if ($isLoggedIn == 2) {
                return $next($request);
            } else if ($isLoggedIn == 1) {
                $leveltype = userType();
                $location = $this->manageRedirect( $userLevel );
                if( $location != null ) {
                    return redirect($location);
                }else {
                    if ($userLevel == 1) {
                        return redirect("astracking/$leveltype/pupil-platform-ast");
                    } else {
                        return redirect("astracking/$leveltype/platform-ast-menu");
                    }
                }
                
            }
        }
    }

    public function manageRedirect( $userLevel ) {
        $school_id = Session::get("school_id");
        $user = Session::get('user');
        $user = json_decode($user);
        dbSchool( $school_id );
        $status = isSchoolEligible( $school_id );
        $to_training = CheckPermissionToRedirect( $user->id, $school_id ); 
        if( $status ) {
            if( $userLevel == '1' )
                return '/ast-next/student-home'; 
            else if( $userLevel == '3' )
                return '/ast-next/admin/getting-started';    
            else if( $school_id == env("UI_UX_GROUP_DASHBOARD") ) 
                return '/ast-next/group-dashboard/implementation';
            else if( $to_training == true ) 
                return '/ast-next/training';
            else
                return '/ast-next';
        }
        return null;
    }

}
