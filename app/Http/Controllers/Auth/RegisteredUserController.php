<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Flow:
     *  1. User fills registration form
     *  2. Controller validates data
     *  3. User model inserts into users table
     *  4. Data stored in SQLite
     *  5. Email verification sent → redirect based on role
     */
    public function store(Request $request): RedirectResponse
    {
        // ── Step 2: Controller validates data ──────────────────────────────
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:15', 'unique:users,phone'],
            'gender' => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        // ── Step 3 & 4: User model inserts into users table (SQLite) ───────
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'] ?? null,
            'password' => $validated['password'],  // auto-hashed via model cast
            'role' => User::ROLE_CITIZEN,      // public registration = citizen only
            'auth_provider' => User::AUTH_EMAIL,
            'password_set' => true,
            'is_active' => true,
        ]);

        // ── Step 5: Fire Registered event (sends verification email) ───────
        event(new Registered($user));

        Auth::login($user);

        // ── Redirect based on role ──────────────────────────────────────────
        return redirect($this->redirectTo($user));
    }

    /**
     * Determine where to redirect the user after registration.
     */
    private function redirectTo(User $user): string
    {
        return match ($user->role) {
            User::ROLE_ADMIN => route('admin.dashboard'),
            User::ROLE_ENGINEER => route('engineer.dashboard'),
            default => route('citizen.complaints.index'),
        };
    }
}
