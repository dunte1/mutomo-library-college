@if($noticeType === 'expiring_soon')
<x-mail::message>
# Membership Expiring Soon

Dear {{ $name }},

Your **{{ $planName }}** subscription will expire in **{{ $daysUntilExpiry }} day(s)**.

<x-mail::panel>
**Plan:** {{ $planName }}<br>
**Expiry Date:** {{ $endDate }}<br>
**Days Remaining:** {{ $daysUntilExpiry }}
</x-mail::panel>

To avoid interruption to your library access, please renew your subscription before the expiry date.

<x-mail::button :url="$subscriptionUrl" color="primary">
Renew Now
</x-mail::button>

If you have any questions, please contact the library.

Thanks,<br>
{{ $libraryName }}
@if($libraryPhone || $libraryEmail)
<br>
{{ $libraryPhone ? 'Tel: ' . $libraryPhone : '' }}{{ $libraryPhone && $libraryEmail ? ' | ' : '' }}{{ $libraryEmail ? 'Email: ' . $libraryEmail : '' }}
@endif
</x-mail::message>

@else

<x-mail::message>
# Membership Expired

Dear {{ $name }},

Your **{{ $planName }}** subscription has **expired** as of **{{ $endDate }}**.

<x-mail::panel>
**Plan:** {{ $planName }}<br>
**Expired On:** {{ $endDate }}
</x-mail::panel>

As a result, you have lost access to premium library features. To regain access, please choose a new subscription plan.

<x-mail::button :url="$plansUrl" color="primary">
View Subscription Plans
</x-mail::button>

We'd love to have you back! If you have any questions, please contact the library.

Thanks,<br>
{{ $libraryName }}
@if($libraryPhone || $libraryEmail)
<br>
{{ $libraryPhone ? 'Tel: ' . $libraryPhone : '' }}{{ $libraryPhone && $libraryEmail ? ' | ' : '' }}{{ $libraryEmail ? 'Email: ' . $libraryEmail : '' }}
@endif
</x-mail::message>
@endif
