<x-mail::message>
# Subscription Renewal Reminder

Dear {{ $name }},

This is a reminder that your **{{ $planName }}** subscription is due for renewal.

<x-mail::panel>
**Plan:** {{ $planName }}<br>
**Current Cycle:** {{ $billingCycle }}<br>
**Amount:** {{ $currency }} {{ $amount }}/{{ $billingCycle }}<br>
**End Date:** {{ $endDate }}<br>
**Days Until Renewal:** {{ $daysUntilRenewal }}
</x-mail::panel>

@if($autoRenew)
Your subscription is set to auto-renew. The payment will be processed on the renewal date.
@else
Your subscription is not set to auto-renew. Please renew your subscription to continue enjoying uninterrupted access to library resources.
@endif

<x-mail::button :url="$subscriptionUrl" color="primary">
Manage Subscription
</x-mail::button>

If you have any questions, please contact the library.

Thanks,<br>
{{ $libraryName }}
@if($libraryPhone || $libraryEmail)
<br>
{{ $libraryPhone ? 'Tel: ' . $libraryPhone : '' }}{{ $libraryPhone && $libraryEmail ? ' | ' : '' }}{{ $libraryEmail ? 'Email: ' . $libraryEmail : '' }}
@endif
</x-mail::message>
