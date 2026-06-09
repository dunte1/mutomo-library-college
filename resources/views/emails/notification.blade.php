@component('mail::message')
# {{ $subjectText }}

{{ $bodyText }}

@component('mail::button', ['url' => config('app.url')])
Visit {{ config('app.name') }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
