<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div
        x-data="{ loginAs: '{{ old('login_as', 'staff') }}' }"
        class="space-y-4"
    >
        <div class="grid grid-cols-2 gap-2 rounded-lg bg-gray-100 p-1">
            <button
                type="button"
                class="rounded-md px-3 py-2 text-sm font-medium transition"
                :class="loginAs === 'staff' ? 'bg-white text-pecit-blue shadow-sm' : 'text-gray-600'"
                @click="loginAs = 'staff'"
            >
                Staff
            </button>
            <button
                type="button"
                class="rounded-md px-3 py-2 text-sm font-medium transition"
                :class="loginAs === 'student' ? 'bg-white text-pecit-blue shadow-sm' : 'text-gray-600'"
                @click="loginAs = 'student'"
            >
                Student
            </button>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <input type="hidden" name="login_as" :value="loginAs">

            <div x-show="loginAs === 'staff'" x-cloak>
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" autocomplete="username" x-bind:required="loginAs === 'staff'" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="current-password" x-bind:required="loginAs === 'staff'" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
            </div>

            <div x-show="loginAs === 'student'" x-cloak>
                <div>
                    <x-input-label for="student_id" value="Student ID" />
                    <x-text-input id="student_id" class="block mt-1 w-full" type="text" name="student_id" :value="old('student_id')" autocomplete="username" x-bind:required="loginAs === 'student'" />
                    <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="last_name" value="Last Name" />
                    <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" autocomplete="family-name" x-bind:required="loginAs === 'student'" />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>

                <p class="mt-2 text-xs text-gray-500">Use your Student ID and last name. Email is kept for notifications only.</p>
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <div x-show="loginAs === 'staff'">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <x-primary-button class="ms-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
