@extends('layouts.psis')

@section('title', 'Profile — PSIS')
@section('page-title', 'Profile')

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="psis-card p-6">
        <h3 class="font-semibold mb-1">Profile Information</h3>
        <p class="text-sm text-slate-500 mb-4">Update your account name and email address.</p>

        <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('patch')

            <div>
                <label for="name" class="block text-sm font-medium mb-1">Name</label>
                <input id="name" name="name" type="text" class="psis-input w-full" value="{{ old('name', $user->name) }}" required autofocus>
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input id="email" name="email" type="email" class="psis-input w-full" value="{{ old('email', $user->email) }}" required>
                @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="psis-btn-primary">Save Profile</button>
            @if (session('status') === 'profile-updated')
                <span class="text-sm text-green-600 ml-2">Saved.</span>
            @endif
        </form>
    </div>

    <div class="psis-card p-6">
        <h3 class="font-semibold mb-1">Change Password</h3>
        <p class="text-sm text-slate-500 mb-4">Use a strong, unique password for your account.</p>

        <form method="post" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            @method('put')

            <div>
                <label for="current_password" class="block text-sm font-medium mb-1">Current Password</label>
                <input id="current_password" name="current_password" type="password" class="psis-input w-full" autocomplete="current-password">
                @error('current_password', 'updatePassword')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium mb-1">New Password</label>
                <input id="password" name="password" type="password" class="psis-input w-full" autocomplete="new-password">
                @error('password', 'updatePassword')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="psis-input w-full" autocomplete="new-password">
                @error('password_confirmation', 'updatePassword')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="psis-btn-primary">Update Password</button>
            @if (session('status') === 'password-updated')
                <span class="text-sm text-green-600 ml-2">Password updated.</span>
            @endif
        </form>
    </div>
</div>
@endsection
