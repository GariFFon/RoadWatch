<x-guest-layout>

    {{-- Avatar + heading --}}
    <div style="text-align:center;margin-bottom:1.5rem;">
        @if(auth()->user()->google_avatar)
            <img src="{{ auth()->user()->google_avatar }}"
                 alt="{{ auth()->user()->name }}"
                 style="width:64px;height:64px;border-radius:50%;border:3px solid #6366f1;box-shadow:0 4px 14px rgba(99,102,241,.3);margin:0 auto .875rem;display:block;">
        @endif
        <h1 style="font-size:1.25rem;font-weight:800;color:#111827;">One last step, {{ auth()->user()->name }}!</h1>
        <p style="font-size:.875rem;color:#6b7280;margin-top:.375rem;">Set a password so you can also sign in with your email.</p>
    </div>

    {{-- Flash info --}}
    @if(session('info'))
        <div class="rw-alert-info">ℹ️ {{ session('info') }}</div>
    @endif

    {{-- Signed-in as banner --}}
    <div style="background:#1e1b4b;border-radius:.625rem;padding:.75rem 1rem;font-size:.8125rem;color:#a5b4fc;margin-bottom:1.25rem;">
        Signing in as <strong style="color:#c7d2fe;">{{ auth()->user()->email }}</strong>
    </div>

    <form method="POST" action="{{ route('password.setup.store') }}">
        @csrf

        <div class="rw-field">
            <label for="password">Set a Password</label>
            <input id="password" type="password" name="password"
                   required autocomplete="new-password" placeholder="Min 8 chars, uppercase, number">
            <p class="rw-hint">Min 8 characters · uppercase · lowercase · number</p>
            @error('password') <p class="rw-error">{{ $message }}</p> @enderror
        </div>

        <div class="rw-field">
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   required autocomplete="new-password" placeholder="Repeat your password">
            @error('password_confirmation') <p class="rw-error">{{ $message }}</p> @enderror
        </div>

        {{-- Why this matters --}}
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:.625rem;padding:.75rem 1rem;font-size:.8125rem;color:#92400e;margin-bottom:1.25rem;">
            🔒 Setting a password lets you log in with email too, and keeps your account secure if you ever lose access to Google.
        </div>

        <div class="rw-flex-between">
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:none;border:none;font-size:.875rem;color:#6b7280;cursor:pointer;text-decoration:underline;font-family:inherit;">
                    Not you? Sign out
                </button>
            </form>

            <button type="submit" class="rw-btn-primary">
                Save Password & Continue →
            </button>
        </div>
    </form>

</x-guest-layout>
