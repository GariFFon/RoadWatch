<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        // Role-based redirect — no generic 'dashboard' route exists in this app
        $homeUrl = match ($request->user()->role) {
            User::ROLE_ADMIN    => route('admin.dashboard', absolute: false),
            User::ROLE_ENGINEER => route('engineer.complaints.index', absolute: false),
            default             => route('citizen.complaints.index', absolute: false),
        };

        return redirect()->intended($homeUrl);
    }
}
