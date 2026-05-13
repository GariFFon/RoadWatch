<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    // -------------------------------------------------------------------------
    // Step 1: Redirect user to Google's consent screen
    // -------------------------------------------------------------------------

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    // -------------------------------------------------------------------------
    // Step 2: Google redirects back here with auth code
    // -------------------------------------------------------------------------

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Google authentication failed. Please try again.']);
        }

        // ── Case A: Existing user with same google_id ─────────────────────
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            // Update avatar and ensure email is marked verified
            $user->update([
                'google_avatar'     => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
                'last_login_at'     => now(),
            ]);

            // If they never set a password, send them to set-password screen
            if ($user->needsPasswordSetup()) {
                return redirect()->route('password.setup');
            }

            return redirect($this->dashboardFor($user));
        }

        // ── Case B: Existing user with same email (merge accounts) ────────
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Link their Google account to the existing email account
            $user->update([
                'google_id'     => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
                'auth_provider' => User::AUTH_GOOGLE,
                'last_login_at' => now(),
                // Mark email verified since Google already verified it
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);

            Auth::login($user, remember: true);

            return redirect($this->dashboardFor($user));
        }

        // ── Case C: Brand-new user — create account ───────────────────────
        $newUser = User::create([
            'name'              => $googleUser->getName(),
            'email'             => $googleUser->getEmail(),
            'google_id'         => $googleUser->getId(),
            'google_avatar'     => $googleUser->getAvatar(),
            'auth_provider'     => User::AUTH_GOOGLE,
            'role'              => User::ROLE_CITIZEN,
            'password'          => null,       // no password yet
            'password_set'      => false,      // ← triggers mandatory setup
            'is_active'         => true,
            'email_verified_at' => now(),      // Google already verified email
        ]);

        Auth::login($newUser, remember: true);
        $newUser->update(['last_login_at' => now()]);

        // Redirect to mandatory set-password screen
        return redirect()->route('password.setup')
            ->with('info', 'Welcome! Please set a password to secure your account.');
    }

    // -------------------------------------------------------------------------
    // Helper: determine dashboard by role
    // -------------------------------------------------------------------------

    private function dashboardFor(User $user): string
    {
        return match ($user->role) {
            User::ROLE_ADMIN    => route('admin.dashboard'),
            User::ROLE_ENGINEER => route('engineer.complaints.index'),
            default             => route('citizen.complaints.index'),
        };
    }
}
