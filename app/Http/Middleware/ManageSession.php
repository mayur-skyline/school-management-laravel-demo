<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class ManageSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
       
        if( $request->has('remember_me') ) {
            $remember_me = $request->get('remember_me');
            $remember_me = filter_var($remember_me, FILTER_VALIDATE_BOOLEAN); 
            if($remember_me) {
                config(['session.lifetime' => 43200]);
            }
        }
        $user = $this->validateToken($request);
        // if( !$user ) {
        //     Session::flush();
        //     return redirect('new-check-step1')
        //             ->withCookie(\Cookie::forget('en'))
        //             ->withCookie(\Cookie::forget('ep'))
        //             ->withCookie(\Cookie::forget('enc_sh'))
        //             ->withCookie(\Cookie::forget('enc_sc'))
        //             ->withCookie(\Cookie::forget('jwt_token'))
        //             ->withCookie(\Cookie::forget('enc_u'));
        // }
        if( $request->hasCookie('en')) {
            $en_user_id = $request->cookie('en'); 
            Log::info("Session Refreshing");
            setSession( $en_user_id ); 
        }
       

        if( Session::get('user') == null && !$request->hasCookie('enc_u') ) {
            Session::flush();
            return redirect('new-check-step1' . '?callback_url=' . urlencode($request->url()))
                    ->withCookie(\Cookie::forget('en'))
                    ->withCookie(\Cookie::forget('ep'))
                    ->withCookie(\Cookie::forget('enc_sh'))
                    ->withCookie(\Cookie::forget('enc_sc'))
                    ->withCookie(\Cookie::forget('jwt_token'))
                    ->withCookie(\Cookie::forget('enc_u'));
        }
       
        return $next($request);
    }

    public function validateToken($request) {
        try {
            $school_id = $request->cookie('enc_sh') ?? session()->get('school_id');
            $url = env('APP_URL') . "api-astnext/logged-in-user?school_id=$school_id";
            if( $request->hasCookie('jwt_token')) {
                $token = $request->cookie('jwt_token');
                $response = Http::withToken($token)->acceptJson()->get($url);
                if( $response->status() == 401) return null;
                else return $response->json();
            }
        }catch (Exception $ex) {
            return null;
        }
    }
}
