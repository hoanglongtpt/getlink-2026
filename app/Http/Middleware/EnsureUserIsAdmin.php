<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
        public function handle(Request $request, Closure $next)
    {
        // Chuyển sang kiểm tra bằng guard admin
        if (! Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::guard('admin')->user();

        if (! $user || ! $user->isAdmin()) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->withErrors(['email' => 'Tài khoản không có quyền Admin.']);
        }

        return $next($request);
    }
}
