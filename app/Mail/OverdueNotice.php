<?php

namespace App\Mail;

use App\Modules\Circulation\Models\BorrowRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BorrowRecord $borrowRecord,
        public int $daysOverdue,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Overdue Book Return - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.overdue-notice',
            with: [
                'name' => $this->borrowRecord->user->name,
                'bookTitle' => $this->borrowRecord->bookCopy?->book?->title ?? 'Unknown',
                'barcode' => $this->borrowRecord->bookCopy?->barcode ?? 'N/A',
                'dueDate' => $this->borrowRecord->due_at->format('d M Y'),
                'daysOverdue' => $this->daysOverdue,
                'libraryName' => config('app.name'),
                'libraryPhone' => config('app.library_phone', ''),
                'libraryEmail' => config('app.library_email', ''),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
