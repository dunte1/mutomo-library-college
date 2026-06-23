<?php

namespace App\Mail;

use App\Modules\Assignments\Models\ReadingAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReadingAssignment extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ReadingAssignment $assignment,
    ) {}

    public function envelope(): Envelope
    {
        $prefix = $this->assignment->type === 'assignment' ? 'New Assignment' : 'New Recommendation';

        return new Envelope(
            subject: $prefix.' - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-reading-assignment',
            with: [
                'studentName' => $this->assignment->student->name,
                'teacherName' => $this->assignment->teacher->name,
                'title' => $this->assignment->title,
                'description' => $this->assignment->description,
                'dueDate' => $this->assignment->due_date?->format('d M Y h:i A'),
                'type' => $this->assignment->type,
                'bookTitle' => $this->assignment->book?->title,
                'libraryName' => config('app.name'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
