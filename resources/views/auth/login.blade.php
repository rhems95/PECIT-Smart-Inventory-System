<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @php($loginAs = old('login_as', 'staff') === 'student' ? 'student' : 'staff')

    <div class="psis-login-switch space-y-4">
        <input type="radio" name="login_tab" id="login-as-staff" value="staff" @checked($loginAs === 'staff')>
        <input type="radio" name="login_tab" id="login-as-student" value="student" @checked($loginAs === 'student')>

        <div class="psis-login-tabs grid grid-cols-2 gap-2 rounded-lg bg-gray-100 p-1" role="tablist" aria-label="Sign in as">
            <label for="login-as-staff" class="rounded-md px-3 py-2 text-sm font-medium text-center cursor-pointer">Staff</label>
            <label for="login-as-student" class="rounded-md px-3 py-2 text-sm font-medium text-center cursor-pointer">Student</label>
        </div>

        <form method="POST" action="{{ route('login') }}" class="psis-login-staff">
            @csrf
            <input type="hidden" name="login_as" value="staff">

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" autocomplete="username" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="current-password" required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="block mt-4">
                <label for="remember_me_staff" class="inline-flex items-center">
                    <input id="remember_me_staff" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-primary-button class="ms-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('login') }}" class="psis-login-student">
            @csrf
            <input type="hidden" name="login_as" value="student">

            <div>
                <x-input-label for="last_name" value="Last Name" />
                <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" autocomplete="family-name" required />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="student_id" value="Student ID" />
                <x-text-input id="student_id" class="block mt-1 w-full" type="password" name="student_id" :value="old('student_id')" autocomplete="username" required />
                <label for="show-student-id" class="mt-2 inline-flex items-center gap-2 text-sm text-gray-600">
                    <input id="show-student-id" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    Show Student ID
                </label>
                <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
            </div>

            <p class="mt-2 text-xs text-gray-500">Use your last name and Student ID. Email is kept for notifications only.</p>

            <div class="block mt-4">
                <label for="remember_me_student" class="inline-flex items-center">
                    <input id="remember_me_student" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button class="ms-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </div>
    <script>
        (function () {
            var box = document.getElementById('show-student-id');
            var field = document.getElementById('student_id');
            if (!box || !field) return;
            box.addEventListener('change', function () {
                field.type = box.checked ? 'text' : 'password';
            });
        })();
    </script>
</x-guest-layout>
