<?php

namespace App\Modules\Assignments\Livewire;

use App\Modules\Assignments\Models\ReadingAssignment;
use Livewire\Component;
use Livewire\WithPagination;

class StudentAssignments extends Component
{
    use WithPagination;

    public string $tab = 'all';

    public string $typeFilter = '';

    public function markComplete(int $id): void
    {
        $assignment = ReadingAssignment::forStudent(auth()->id())->findOrFail($id);
        $assignment->markAsCompleted();
        session()->flash('message', 'Marked as completed.');

        if (is_null($assignment->viewed_at)) {
            $assignment->markAsViewed();
        }
    }

    public function getAssignmentsProperty()
    {
        $query = ReadingAssignment::forStudent(auth()->id())
            ->with(['teacher', 'book', 'digitalAsset']);

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->tab === 'pending') {
            $query->whereIn('status', [ReadingAssignment::STATUS_PENDING, ReadingAssignment::STATUS_IN_PROGRESS]);
        } elseif ($this->tab === 'completed') {
            $query->where('status', ReadingAssignment::STATUS_COMPLETED);
        } elseif ($this->tab === 'overdue') {
            $query->where('status', ReadingAssignment::STATUS_OVERDUE);
        }

        return $query->latest()->paginate(15);
    }

    public function render()
    {
        $assignments = $this->assignments;

        foreach ($assignments as $assignment) {
            if (is_null($assignment->viewed_at)) {
                $assignment->markAsViewed();
            }
        }

        return view('assignments::livewire.student-assignments', [
            'assignments' => $assignments,
        ])->layout('layouts.app');
    }
}
