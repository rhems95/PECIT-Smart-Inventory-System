<x-mail::message>
# {{ $alertTitle }}

@if ($recipientName)
Hello {{ $recipientName }},
@else
Hello,
@endif

{{ $alertMessage }}

@if ($actionUrl)
<x-mail::button :url="$actionUrl" color="primary">
View in PSIS
</x-mail::button>
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
