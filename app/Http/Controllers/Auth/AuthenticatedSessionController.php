<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $role = $request->user()->role;

        $destination = match ($role) {
            'admin' => route('admin.dashboard'),
            'engineer' => route('engineer.complaints.index'),
            'citizen' => route('citizen.complaints.index'),
            default => route('home'),
        };

        // Use intended() only when the stored URL is a safe web page (not an API endpoint).
        $intended = session()->pull('url.intended');
        if ($intended && ! str_contains($intended, '/api/')) {
            return redirect($intended);
        }

        return redirect($destination);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
