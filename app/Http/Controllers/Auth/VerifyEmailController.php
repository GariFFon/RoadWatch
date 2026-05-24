<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $homeUrl = $this->roleHomeUrl($request->user());

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($homeUrl.'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended($homeUrl.'?verified=1');
    }

    /**
     * Resolve the correct home URL based on the user's role.
     */
    private function roleHomeUrl(User $user): string
    {
        return match ($user->role) {
            User::ROLE_ADMIN => route('admin.dashboard', absolute: false),
            User::ROLE_ENGINEER => route('engineer.complaints.index', absolute: false),
            default => route('citizen.complaints.index', absolute: false),
        };
    }
}
