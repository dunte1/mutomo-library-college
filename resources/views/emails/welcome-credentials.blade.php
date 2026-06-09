@component('mail::message')
# Welcome to {{ $libraryName }}

Hello **{{ $name }}**,

Your library account has been created. You can now log in to the library system using the credentials below.

@component('mail::panel')
**Email:** {{ $email }}

**Password:** {{ $password }}
@endcomponent

@component('mail::button', ['url' => $loginUrl, 'color' => 'primary'])
Login to Your Account
@endcomponent

For security, please change your password after your first login.

If you have any questions, feel free to contact the library.

Thanks,<br>
{{ $libraryName }}

@if($libraryPhone || $libraryEmail)
---
**Contact:**<br>
@if($libraryPhone) Phone: {{ $libraryPhone }}<br>@endif
@if($libraryEmail) Email: {{ $libraryEmail }}@endif
@endif
@endcomponent
