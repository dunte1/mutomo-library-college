<x-mail::message>
# Subscription Activated!

Dear {{ $name }},

Your **{{ $planName }}** subscription has been activated successfully.

<x-mail::panel>
**Plan:** {{ $planName }}<br>
**Amount:** {{ $currency }} {{ $amount }}/{{ $billingCycle }}<br>
**Start Date:** {{ $startDate }}<br>
**End Date:** {{ $endDate }}<br>
**Auto-Renew:** {{ $autoRenew }}
</x-mail::panel>

You now have full access to all library resources included in your plan, including:
- 📚 Book borrowing privileges
- 📖 Digital library access
- 🔍 Catalog search and reservations
- 📊 Reading history tracking

<x-mail::button :url="$loginUrl" color="primary">
Access Your Account
</x-mail::button>

If you have any questions about your subscription, please visit your subscription management page or contact the library.

Thanks,<br>
{{ $libraryName }}
@if($libraryPhone || $libraryEmail)
<br>
{{ $libraryPhone ? 'Tel: ' . $libraryPhone : '' }}{{ $libraryPhone && $libraryEmail ? ' | ' : '' }}{{ $libraryEmail ? 'Email: ' . $libraryEmail : '' }}
@endif
</x-mail::message>
