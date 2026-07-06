@component('mail::message')
# Welcome to {{ $libraryName }}

Hello **{{ $name }}**,

Your library account has been created. To get started, please set your password using the button below.

@component('mail::panel')
**Email:** {{ $email }}
@endcomponent

@component('mail::button', ['url' => $resetLink, 'color' => 'primary'])
Set Your Password
@endcomponent

If the button above doesn't work, copy and paste this link into your browser:
{{ $resetLink }}

After setting your password, you can log in at:

@component('mail::button', ['url' => $loginUrl, 'color' => 'success'])
Login to Your Account
@endcomponent

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
