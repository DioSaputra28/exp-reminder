<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.admin-login');
    }

    /**
     * Handle admin login.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Email atau password salah.');
        }

        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Akun ini bukan akun admin.');
        }

        if (! $user->is_active) {
            Auth::logout();

            return back()
                ->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        $user->update(['last_login_at' => now()]);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Log the admin out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
