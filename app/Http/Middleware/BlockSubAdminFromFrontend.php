<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSubAdminFromFrontend
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Nếu user chưa đăng nhập, cho phép truy cập
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        
        // Nếu là sub_admin, chặn truy cập vào frontend
        if ($user->role === 'sub_admin') {
            // Redirect về trang sub-admin dashboard
            request()->session()->flash('error', 'Sub Admin không được phép truy cập vào các trang user');
            return redirect()->route('sub-admin.dashboard');
        }
        
        // Cho phép tất cả các role khác (admin, user) truy cập
        return $next($request);
    }
}