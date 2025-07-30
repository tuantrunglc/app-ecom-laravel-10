<?php

namespace App\Http\Middleware;

use Closure;

class Admin
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
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        if ($user->role === 'admin') {
            return $next($request);
        }
        
        request()->session()->flash('error', 'Bạn không có quyền truy cập trang này');
        
        // Redirect theo role
        if ($user->role === 'sub_admin') {
            return redirect()->route('sub-admin.dashboard');
        } else {
            return redirect()->route('user');
        }
    }
}
