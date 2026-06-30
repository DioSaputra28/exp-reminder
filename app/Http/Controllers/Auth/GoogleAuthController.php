<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     * Socialite automatically generates and stores a "state" parameter
     * in the session for CSRF protection.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Handle the callback from Google OAuth.
     */
    public function callback(Request $request): RedirectResponse
    {
        // 1. Socialite validates the "state" parameter automatically.
        //    InvalidStateException is thrown if state mismatch (CSRF attempt).
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $e) {
            Log::warning('Google OAuth state mismatch — possible CSRF attempt.', [
                'ip' => $request->ip(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Sesi login tidak valid. Silakan coba lagi.');
        } catch (\Exception $e) {
            Log::error('Google OAuth error: '.$e->getMessage());

            return redirect()->route('login')
                ->with('error', 'Login dengan Google gagal. Silakan coba lagi.');
        }

        // 2. Require a verified email from Google.
        if (! filter_var($googleUser->getEmail(), FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('login')
                ->with('error', 'Email Google tidak valid.');
        }

        // 3. Guard against email collision with existing admin accounts.
        //    If an email is already in use by an admin (password-based account),
        //    we refuse to link a Google account to it.
        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser && $existingUser->role === UserRole::Admin && ! $existingUser->google_id) {
            Log::warning('OAuth login blocked — email belongs to admin account.', [
                'email' => $googleUser->getEmail(),
                'ip' => $request->ip(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Email ini terdaftar sebagai akun admin. Gunakan halaman login admin.');
        }

        // 4. Find by google_id first (most secure), then fall back to email.
        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())
                ->whereNull('google_id')
                ->whereNot('role', UserRole::Admin)
                ->first();

        if ($user) {
            // Update profile data and link google_id if not yet linked.
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'last_login_at' => now(),
            ]);
        } else {
            // First-time login — auto-register as User role.
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'role' => UserRole::User,
                'is_active' => true,
                'last_login_at' => now(),
            ]);
        }

        // 5. Check account is active before allowing login.
        if (! $user->is_active) {
            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi administrator.');
        }

        // 6. Prevent session fixation: regenerate session ID on login.
        $request->session()->regenerate();
        $request->session()->forget('url.intended'); // Clear any stored URL to avoid redirect to 403

        Auth::login($user, remember: true);

        return redirect()->route('dashboard');
    }

    /**
     * Log the user out and fully invalidate the session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate session entirely and rotate CSRF token.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
