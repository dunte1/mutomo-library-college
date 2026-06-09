<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Circulation\Models\BorrowRecord;
use Illuminate\Database\Seeder;

class CirculationSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('email', 'student@ollmchs.ac.ke')->first();
        $lecturer = User::where('email', 'lecturer@ollmchs.ac.ke')->first();
        $librarian = User::where('email', 'librarian@ollmchs.ac.ke')->first();
        $hod = User::where('email', 'hod@ollmchs.ac.ke')->first();

        if (!$student) return;

        $availableCopies = BookCopy::where('status', 'available')->take(5)->get();

        if ($availableCopies->count() < 2) return;

        // Active borrow for student
        $copy1 = $availableCopies->get(0);
        BorrowRecord::create([
            'user_id' => $student->id,
            'book_copy_id' => $copy1->id,
            'borrowed_at' => now()->subDays(5),
            'due_at' => now()->addDays(9),
            'status' => BorrowRecord::STATUS_ACTIVE,
            'issued_by' => $librarian?->id,
            'created_by' => $librarian?->id,
        ]);
        $copy1->update(['status' => 'borrowed']);

        // Overdue borrow for student
        $copy2 = $availableCopies->get(1);
        BorrowRecord::create([
            'user_id' => $student->id,
            'book_copy_id' => $copy2->id,
            'borrowed_at' => now()->subDays(20),
            'due_at' => now()->subDays(6),
            'status' => BorrowRecord::STATUS_OVERDUE,
            'issued_by' => $librarian?->id,
            'created_by' => $librarian?->id,
        ]);
        $copy2->update(['status' => 'borrowed']);

        // Active borrow for lecturer
        if ($lecturer && $availableCopies->count() > 2) {
            $copy3 = $availableCopies->get(2);
            BorrowRecord::create([
                'user_id' => $lecturer->id,
                'book_copy_id' => $copy3->id,
                'borrowed_at' => now()->subDays(3),
                'due_at' => now()->addDays(27),
                'status' => BorrowRecord::STATUS_ACTIVE,
                'issued_by' => $librarian?->id,
                'created_by' => $librarian?->id,
            ]);
            $copy3->update(['status' => 'borrowed']);
        }

        // Returned record
        if ($availableCopies->count() > 3) {
            $copy4 = $availableCopies->get(3);
            BorrowRecord::create([
                'user_id' => $student->id,
                'book_copy_id' => $copy4->id,
                'borrowed_at' => now()->subDays(15),
                'due_at' => now()->subDays(1),
                'returned_at' => now()->subDays(1),
                'received_by' => $librarian?->id,
                'status' => BorrowRecord::STATUS_RETURNED,
                'issued_by' => $librarian?->id,
                'created_by' => $librarian?->id,
            ]);
        }
    }
}
