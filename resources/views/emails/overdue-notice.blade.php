<x-mail::message>
# Overdue Book Notice

Dear {{ $name }},

This is a reminder that the following book is **overdue** and needs to be returned to the **{{ $libraryName }}** immediately.

<x-mail::panel>
**Book:** {{ $bookTitle }}<br>
**Barcode:** {{ $barcode }}<br>
**Due Date:** {{ $dueDate }}<br>
**Days Overdue:** {{ $daysOverdue }}
</x-mail::panel>

Please return the book as soon as possible to avoid further fines. Overdue fines are calculated at KES 50 per day.

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
