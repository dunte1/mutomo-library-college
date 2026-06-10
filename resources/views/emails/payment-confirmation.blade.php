<x-mail::message>
# Payment Confirmed

Dear {{ $name }},

This is to confirm that we have received your payment successfully.

<x-mail::panel>
**Transaction #:** {{ $transactionNumber }}<br>
**Amount Paid:** {{ $currency }} {{ $amount }}<br>
**Payment Method:** {{ $paymentMethod }}<br>
**Reference:** {{ $reference ?: 'N/A' }}<br>
**Date:** {{ $paidAt }}<br>
**Description:** {{ $description }}
</x-mail::panel>

@if($receiptUrl)
<x-mail::button :url="$receiptUrl" color="primary">
View / Download Receipt
</x-mail::button>
@endif

Thank you for your payment.

If you have any questions, please contact the library.

Thanks,<br>
{{ $libraryName }}
@if($libraryPhone || $libraryEmail)
<br>
{{ $libraryPhone ? 'Tel: ' . $libraryPhone : '' }}{{ $libraryPhone && $libraryEmail ? ' | ' : '' }}{{ $libraryEmail ? 'Email: ' . $libraryEmail : '' }}
@endif
</x-mail::message>
