<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <style>
            [x-cloak]{display:none!important}
            .psis-login-switch input[name="login_tab"]{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
            .psis-login-staff,.psis-login-student{display:none}
            #login-as-staff:checked ~ .psis-login-staff{display:block}
            #login-as-student:checked ~ .psis-login-student{display:block}
            .psis-login-tabs label{color:#4b5563}
            #login-as-staff:checked ~ .psis-login-tabs label[for="login-as-staff"],
            #login-as-student:checked ~ .psis-login-tabs label[for="login-as-student"]{background:#fff;color:#0B3C91;box-shadow:0 1px 2px rgba(0,0,0,.06)}
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="text-center">
                <a href="/" class="inline-flex flex-col items-center gap-2">
                    <img src="{{ asset('images/pecit-logo.png') }}" alt="PECIT" class="w-20 h-20 object-contain">
                    <span class="text-sm font-semibold text-pecit-blue">PECIT Smart Inventory System</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
