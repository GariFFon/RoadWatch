<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SetPasswordController extends Controller
{
    /**
     * Show the mandatory set-password screen.
     * Only accessible to Google users who haven't set a password yet.
     */
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        // If user already has a password, send them to their dashboard
        if (! $user->needsPasswordSetup()) {
            return redirect($this->dashboardFor($user));
        }

        return view('auth.set-password');
    }

    /**
     * Store the new password.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->needsPasswordSetup()) {
            return redirect($this->dashboardFor($user));
        }

        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ]);

        // Set the password and mark setup as complete
        $user->forceFill([
            'password' => $request->password,  // auto-hashed via cast
            'password_set' => true,
        ])->save();

        return redirect($this->dashboardFor($user))
            ->with('success', '🎉 Password set! Your account is fully secured.');
    }

    private function dashboardFor($user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'engineer' => route('engineer.dashboard'),
            default => route('citizen.complaints.index'),
        };
    }
}
