<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::guard('admin')->user();
            
            if (!$user->isAdmin()) {
                Auth::guard('admin')->logout();
                return back()->withErrors([
                    'email' => 'Tài khoản không có quyền truy cập Admin.',
                ]);
            }
            
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        
        // Không invalidate toàn bộ session để tránh làm ảnh hưởng đến tài khoản client đang đăng nhập
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
