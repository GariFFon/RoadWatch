<x-guest-layout>

    {{-- Session status --}}
    @if (session('status'))
        <div class="rw-alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->has('google'))
        <div class="rw-alert-error">{{ $errors->first('google') }}</div>
    @endif

    {{-- Page heading --}}
    <div style="text-align:center;margin-bottom:1.5rem;">
        <h1 style="font-size:1.375rem;font-weight:800;color:#111827;letter-spacing:-.02em;">Welcome back</h1>
        <p style="font-size:.875rem;color:#6b7280;margin-top:.25rem;">Sign in to your RoadWatch account</p>
    </div>

    {{-- Google OAuth --}}
    <a href="{{ route('auth.google') }}" class="rw-google-btn">
        <svg width="20" height="20" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#EA4335" d="M24 9.5c3.2 0 6 1.1 8.2 3.2l6.1-6.1C34.5 3.1 29.6 1 24 1 14.9 1 7.2 6.4 3.7 14.1l7.2 5.6C12.6 13.3 17.8 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2l7.4 5.7c4.3-4 6.2-9.9 6.2-16.9z"/>
            <path fill="#FBBC05" d="M10.9 28.5C10.3 26.8 10 25 10 23s.3-3.8.9-5.5L3.7 11.9C1.9 15.2 1 18.9 1 23s.9 7.8 2.7 11.1l7.2-5.6z"/>
            <path fill="#34A853" d="M24 46c5.6 0 10.3-1.8 13.7-5l-7.4-5.7c-1.9 1.3-4.3 2.1-6.3 2.1-6.2 0-11.4-3.8-13.1-9.2l-7.2 5.6C7.2 41.6 14.9 46 24 46z"/>
        </svg>
        Continue with Google
    </a>

    <div class="rw-divider"><span>or sign in with email</span></div>

    {{-- Email / Password form --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="rw-field">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username" placeholder="you@example.com">
            @error('email') <p class="rw-error">{{ $message }}</p> @enderror
        </div>

        <div class="rw-field">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.375rem;">
                <label for="password" style="margin-bottom:0;">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="rw-link" style="font-size:.78rem;">Forgot password?</a>
                @endif
            </div>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password" placeholder="••••••••">
            @error('password') <p class="rw-error">{{ $message }}</p> @enderror
        </div>

        <div style="margin-bottom:1.25rem;">
            <label class="rw-remember">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Remember me for 30 days</span>
            </label>
        </div>

        <button type="submit" class="rw-btn-primary" style="width:100%;">
            Sign in →
        </button>
    </form>

    <p class="rw-form-footer">
        Don't have an account?
        <a href="{{ route('register') }}" class="rw-link">Create one</a>
    </p>

</x-guest-layout>
