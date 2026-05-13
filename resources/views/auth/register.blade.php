<x-guest-layout>
    {{-- ── Page title ── --}}
    <x-slot name="title">Create Account — RoadWatch</x-slot>

    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create your account</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Join RoadWatch and help improve your city's roads
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- ── Step 2: Controller validates these fields ── --}}

        {{-- Name --}}
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input
                id="name" name="name" type="text"
                class="mt-1 block w-full" :value="old('name')"
                required autofocus autocomplete="name"
                placeholder="e.g. Gourav Dash"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input
                id="email" name="email" type="email"
                class="mt-1 block w-full" :value="old('email')"
                required autocomplete="username"
                placeholder="you@example.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Phone --}}
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input
                id="phone" name="phone" type="tel"
                class="mt-1 block w-full" :value="old('phone')"
                required autocomplete="tel"
                placeholder="e.g. 9876543210"
            />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        {{-- Gender --}}
        <div class="mt-4">
            <x-input-label for="gender" :value="__('Gender (optional)')" />
            <select
                id="gender" name="gender"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            >
                <option value="">-- Select --</option>
                <option value="male"             {{ old('gender') === 'male'             ? 'selected' : '' }}>Male</option>
                <option value="female"           {{ old('gender') === 'female'           ? 'selected' : '' }}>Female</option>
                <option value="other"            {{ old('gender') === 'other'            ? 'selected' : '' }}>Other</option>
                <option value="prefer_not_to_say"{{ old('gender') === 'prefer_not_to_say'? 'selected' : '' }}>Prefer not to say</option>
            </select>
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                id="password" name="password" type="password"
                class="mt-1 block w-full"
                required autocomplete="new-password"
            />
            <p class="mt-1 text-xs text-gray-400">Min 8 characters, uppercase, lowercase & number</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Confirm Password --}}
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input
                id="password_confirmation" name="password_confirmation" type="password"
                class="mt-1 block w-full"
                required autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Submit --}}
        <div class="mt-6 flex items-center justify-between">
            <a class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline"
               href="{{ route('login') }}">
                {{ __('Already have an account?') }}
            </a>

            <x-primary-button>
                {{ __('Create Account') }}
            </x-primary-button>
        </div>

        {{-- Google OAuth divider --}}
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-white dark:bg-gray-900 px-3 text-gray-400">or</span>
            </div>
        </div>

        <a href="{{ route('auth.google') }}"
           class="flex w-full items-center justify-center gap-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
        </a>
    </form>
</x-guest-layout>

