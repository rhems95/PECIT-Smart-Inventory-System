<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isStudentLogin()) {
            return [
                'login_as' => ['nullable', 'in:student,staff'],
                'student_id' => ['required', 'string', 'max:50'],
                'last_name' => ['required', 'string', 'max:255'],
            ];
        }

        return [
            'login_as' => ['nullable', 'in:student,staff'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('login_as')) {
            $this->merge(['login_as' => 'staff']);
        }
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if ($this->isStudentLogin()) {
            $this->authenticateStudent();
        } else {
            $this->authenticateStaff();
        }

        RateLimiter::clear($this->throttleKey());
    }

    protected function isStudentLogin(): bool
    {
        return $this->input('login_as', 'staff') === 'student';
    }

    /**
     * @throws ValidationException
     */
    protected function authenticateStaff(): void
    {
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if (Auth::user()?->hasRole('Student')) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Students must sign in with Student ID and last name.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function authenticateStudent(): void
    {
        $studentId = trim((string) $this->input('student_id'));
        $lastName = trim((string) $this->input('last_name'));

        $user = User::query()
            ->where('employee_id', $studentId)
            ->where('is_active', true)
            ->first();

        $matches = $user
            && $user->hasRole('Student')
            && $user->last_name
            && strcasecmp($user->last_name, $lastName) === 0;

        if (! $matches) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'student_id' => 'These credentials do not match our records.',
            ]);
        }

        Auth::login($user, $this->boolean('remember'));
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $field = $this->isStudentLogin() ? 'student_id' : 'email';

        throw ValidationException::withMessages([
            $field => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        $identity = $this->isStudentLogin()
            ? (string) $this->string('student_id')
            : (string) $this->string('email');

        return Str::transliterate(Str::lower($identity).'|'.$this->ip());
    }
}
