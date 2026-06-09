<?php

namespace App\Services;

use App\Modules\Catalog\Models\Book;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Members\Models\Member;
use Illuminate\Support\Facades\Response;

class ExportService
{
    public function exportBooksCsv(): \Illuminate\Http\Response
    {
        $books = Book::with(['authors', 'publisher', 'category'])->get();

        $headers = ['Title', 'Authors', 'ISBN', 'Publisher', 'Category', 'Year', 'Pages', 'Edition', 'Language', 'Total Copies', 'Available'];

        $callback = function () use ($books, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($books as $book) {
                fputcsv($file, [
                    $book->title,
                    $book->authors->pluck('name')->implode('; '),
                    $book->isbn,
                    $book->publisher?->name,
                    $book->category?->name,
                    $book->publication_year,
                    $book->pages,
                    $book->edition,
                    $book->language,
                    $book->total_copies,
                    $book->available_count,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="books-export-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function exportCirculationCsv(?string $status = null): \Illuminate\Http\Response
    {
        $query = BorrowRecord::with(['user', 'copy.book', 'copy.book.authors']);

        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'overdue') {
            $query->overdue();
        }

        $records = $query->latest('borrowed_at')->get();

        $headers = ['Borrower', 'Book', 'Authors', 'Barcode', 'Borrowed At', 'Due At', 'Returned At', 'Status', 'Days Overdue'];

        $callback = function () use ($records, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->user?->name,
                    $record->copy?->book?->title,
                    $record->copy?->book?->authors->pluck('name')->implode('; '),
                    $record->copy?->barcode,
                    $record->borrowed_at?->format('Y-m-d'),
                    $record->due_at?->format('Y-m-d'),
                    $record->returned_at?->format('Y-m-d'),
                    $record->status,
                    $record->daysOverdue(),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="circulation-export-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function exportMembersCsv(): \Illuminate\Http\Response
    {
        $members = Member::with(['department', 'program'])->get();

        $headers = ['Name', 'Email', 'Phone', 'Department', 'Program', 'Admission Number', 'Member Type', 'Status', 'Registered At'];

        $callback = function () use ($members, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($members as $member) {
                fputcsv($file, [
                    $member->full_name,
                    $member->email,
                    $member->phone,
                    $member->department?->name,
                    $member->program?->name,
                    $member->admission_number,
                    $member->membership_type,
                    ucfirst($member->status),
                    $member->created_at?->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="members-export-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
