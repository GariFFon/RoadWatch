<x-guest-layout>
    <div class="mb-6 text-center">
        {{-- Google avatar --}}
        @if(auth()->user()->google_avatar)
            <img
                src="{{ auth()->user()->google_avatar }}"
                alt="{{ auth()->user()->name }}"
                class="mx-auto mb-4 h-16 w-16 rounded-full border-2 border-indigo-500 shadow"
            >
        @endif

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            One last step, {{ auth()->user()->name }}!
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Set a password so you can also log in with your email.
        </p>
    </div>

    {{-- Flash messages --}}
    @if(session('info'))
        <div class="mb-4 rounded-md bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 p-3 text-sm text-blue-700 dark:text-blue-300">
            {{ session('info') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.setup.store') }}">
        @csrf

        {{-- Email (read-only, for context) --}}
        <div class="mb-4 rounded-md bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
            Signing in as <span class="font-semibold">{{ auth()->user()->email }}</span>
        </div>

        {{-- New Password --}}
        <div>
            <x-input-label for="password" :value="__('Set a Password')" />
            <x-text-input
                id="password"
                name="password"
                type="password"
                class="mt-1 block w-full"
                required
                autocomplete="new-password"
            />
            <p class="mt-1 text-xs text-gray-400">
                Min 8 characters · uppercase · lowercase · number
            </p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Confirm Password --}}
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                class="mt-1 block w-full"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Why this matters --}}
        <div class="mt-4 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-3 text-xs text-amber-700 dark:text-amber-300">
            🔒 Setting a password lets you log in with email too, and keeps your account secure if you ever lose access to Google.
        </div>

        {{-- Submit --}}
        <div class="mt-6 flex items-center justify-between">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline">
                    Not you? Sign out
                </button>
            </form>

            <x-primary-button>
                {{ __('Save Password & Continue') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
