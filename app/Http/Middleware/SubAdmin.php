<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        if ($user->role === 'sub_admin') {
            return $next($request);
        }
        
        // Nếu là admin, cho phép truy cập sub-admin routes
        if ($user->role === 'admin') {
            return $next($request);
        }

        request()->session()->flash('error', 'Bạn không có quyền truy cập trang này');
        return redirect()->route($user->role);
    }
}
