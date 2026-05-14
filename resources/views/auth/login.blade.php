<x-guest-layout>

    {{-- Session / error alerts --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($errors->has('google'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;font-size:.875rem;margin-bottom:1rem;">
            {{ $errors->first('google') }}
        </div>
    @endif

    {{-- ── Google OAuth button ─────────────────────────────── --}}
    <a href="{{ route('auth.google') }}"
       style="display:flex;align-items:center;justify-content:center;gap:.625rem;width:100%;padding:.625rem 1rem;
              border:1.5px solid #e5e7eb;border-radius:.5rem;background:#fff;color:#374151;
              font-size:.9375rem;font-weight:600;text-decoration:none;transition:all .2s;
              box-shadow:0 1px 3px rgba(0,0,0,.06);"
       onmouseover="this.style.background='#f9fafb';this.style.borderColor='#d1d5db';this.style.boxShadow='0 2px 6px rgba(0,0,0,.1)';"
       onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb';this.style.boxShadow='0 1px 3px rgba(0,0,0,.06)';">
        {{-- Google G logo SVG --}}
        <svg width="20" height="20" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#EA4335" d="M24 9.5c3.2 0 6 1.1 8.2 3.2l6.1-6.1C34.5 3.1 29.6 1 24 1 14.9 1 7.2 6.4 3.7 14.1l7.2 5.6C12.6 13.3 17.8 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2l7.4 5.7c4.3-4 6.2-9.9 6.2-16.9z"/>
            <path fill="#FBBC05" d="M10.9 28.5C10.3 26.8 10 25 10 23s.3-3.8.9-5.5L3.7 11.9C1.9 15.2 1 18.9 1 23s.9 7.8 2.7 11.1l7.2-5.6z"/>
            <path fill="#34A853" d="M24 46c5.6 0 10.3-1.8 13.7-5l-7.4-5.7c-1.9 1.3-4.3 2.1-6.3 2.1-6.2 0-11.4-3.8-13.1-9.2l-7.2 5.6C7.2 41.6 14.9 46 24 46z"/>
        </svg>
        Continue with Google
    </a>

    {{-- ── Divider ─────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;gap:.75rem;margin:1.25rem 0;">
        <div style="flex:1;height:1px;background:#e5e7eb;"></div>
        <span style="font-size:.8125rem;color:#9ca3af;font-weight:500;">or sign in with email</span>
        <div style="flex:1;height:1px;background:#e5e7eb;"></div>
    </div>

    {{-- ── Email / Password form ───────────────────────────── --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                          :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                          type="password" name="password"
                          required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                       name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                   href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    {{-- ── Register link ───────────────────────────────────── --}}
    <p style="text-align:center;font-size:.8125rem;color:#6b7280;margin-top:1.25rem;">
        Don't have an account?
        <a href="{{ route('register') }}"
           style="color:#4f46e5;font-weight:600;text-decoration:none;"
           onmouseover="this.style.textDecoration='underline'"
           onmouseout="this.style.textDecoration='none'">
            Create one
        </a>
    </p>

</x-guest-layout>
