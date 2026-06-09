<x-mail::message>
# {{ ucfirst($type) }} from {{ $teacherName }}

Dear {{ $studentName }},

You have received a new **{{ $type }}** from **{{ $teacherName }}**.

<x-mail::panel>
**Title:** {{ $title }}
@if($description)
**Description:** {{ $description }}
@endif
@if($dueDate)
**Due Date:** {{ $dueDate }}
@endif
@if($bookTitle)
**Book:** {{ $bookTitle }}
@endif
</x-mail::panel>

Please log in to the library system to view the full details.

<x-mail::button :url="url('/assignments/student')">
View My Assignments
</x-mail::button>

Thanks,<br>
{{ $libraryName }}
</x-mail::message>
