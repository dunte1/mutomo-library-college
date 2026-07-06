<x-mail::message>
# Book Due Date Reminder

Dear {{ $name }},

This is a reminder that the following book is due for return in **{{ $daysUntilDue }} day(s)**.

<x-mail::panel>
**Book:** {{ $bookTitle }}<br>
**Barcode:** {{ $barcode }}<br>
**Due Date:** {{ $dueDate }}
</x-mail::panel>

Please return the book on or before the due date to avoid overdue fines. Overdue fines are calculated at KES {{ number_format($finePerDay, 2) }} per day.

@if($daysUntilDue <= 1)
You can renew the book online if you need more time (subject to renewal limits).
@endif

<x-mail::button :url="url('/circulation')">
View My Borrows
</x-mail::button>

If you have already returned the book, please disregard this notice.

Thanks,<br>
{{ $libraryName }}
@if($libraryPhone || $libraryEmail)
<br>
{{ $libraryPhone ? 'Tel: ' . $libraryPhone : '' }}{{ $libraryPhone && $libraryEmail ? ' | ' : '' }}{{ $libraryEmail ? 'Email: ' . $libraryEmail : '' }}
@endif
</x-mail::message>
