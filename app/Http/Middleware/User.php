<?php

namespace App\Http\Middleware;

use Closure;

class User
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Kiểm tra session user hoặc auth user
        if(empty(session('user')) && !auth()->check()){
            return redirect()->route('login.form');
        }
        
        // Nếu sử dụng auth và là sub_admin, redirect
        if(auth()->check() && auth()->user()->role === 'sub_admin'){
            request()->session()->flash('error', 'Sub Admin không được phép truy cập trang user');
            return redirect()->route('sub-admin.dashboard');
        }
        
        return $next($request);
    }
}
