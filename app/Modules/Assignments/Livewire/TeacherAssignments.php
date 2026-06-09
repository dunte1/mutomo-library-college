<?php

namespace App\Modules\Assignments\Livewire;

use App\Mail\NewReadingAssignment;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use App\Modules\Assignments\Models\ReadingAssignment;
use App\Modules\Catalog\Models\Book;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherAssignments extends Component
{
    use WithPagination;

    public string $tab = 'all';
    public string $typeFilter = '';
    public string $statusFilter = '';
    public string $search = '';

    public bool $showForm = false;
    public bool $editing = false;
    public ?int $editId = null;

    public string $assignTo = 'individual';
    public $student_id = '';
    public $program_id = '';
    public $department_id = '';
    public $book_id = '';
    public $digital_asset_id = '';
    public $title = '';
    public $description = '';
    public $due_date = '';
    public $type = 'assignment';
    public $notes = '';

    public ?int $progressAssignmentId = null;

    protected function getListeners(): array
    {
        return [
            '$refresh',
        ];
    }

    protected $rules = [
        'assignTo' => 'required|in:individual,program,department',
        'student_id' => 'required_if:assignTo,individual|exists:users,id',
        'program_id' => 'required_if:assignTo,program|exists:programs,id',
        'department_id' => 'required_if:assignTo,department|exists:departments,id',
        'book_id' => 'nullable|exists:books,id',
        'digital_asset_id' => 'nullable|exists:digital_assets,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'due_date' => 'nullable|date',
        'type' => 'required|in:assignment,recommendation',
        'notes' => 'nullable|string',
    ];

    public function mount(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->assignTo = 'individual';
        $this->student_id = '';
        $this->program_id = '';
        $this->department_id = '';
        $this->book_id = '';
        $this->digital_asset_id = '';
        $this->title = '';
        $this->description = '';
        $this->due_date = '';
        $this->type = 'assignment';
        $this->notes = '';
        $this->editId = null;
        $this->editing = false;
    }

    protected function resolveStudents(): array
    {
        if ($this->assignTo === 'individual') {
            return [$this->student_id];
        }

        if ($this->assignTo === 'program') {
            return User::byRole('student')->active()
                ->where('program_id', $this->program_id)
                ->pluck('id')
                ->toArray();
        }

        if ($this->assignTo === 'department') {
            return User::byRole('student')->active()
                ->where('department_id', $this->department_id)
                ->pluck('id')
                ->toArray();
        }

        return [];
    }

    protected function sendNotifications(ReadingAssignment $assignment): void
    {
        $notificationService = app(NotificationService::class);

        $notificationService->send(
            $assignment->student,
            $assignment->type === 'assignment' ? 'assignment' : 'recommendation',
            $assignment->type === 'assignment' ? 'New Reading Assignment' : 'New Recommendation',
            "{$assignment->title} — assigned by {$assignment->teacher->name}.",
            $assignment->type === 'assignment' ? 'book-open' : 'star',
            route('assignments.my'),
        );

        try {
            Mail::to($assignment->student->email)
                ->send(new NewReadingAssignment($assignment));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function create(): void
    {
        $this->validate();

        $studentIds = $this->resolveStudents();

        if (empty($studentIds)) {
            session()->flash('error', 'No students found for the selected group.');
            return;
        }

        $teacherId = auth()->id();
        $createdCount = 0;

        foreach ($studentIds as $sid) {
            $assignment = ReadingAssignment::create([
                'teacher_id' => $teacherId,
                'student_id' => $sid,
                'book_id' => $this->book_id ?: null,
                'digital_asset_id' => $this->digital_asset_id ?: null,
                'program_id' => $this->assignTo === 'program' ? $this->program_id : null,
                'department_id' => $this->assignTo === 'department' ? $this->department_id : null,
                'title' => $this->title,
                'description' => $this->description,
                'due_date' => $this->due_date ?: null,
                'type' => $this->type,
                'notes' => $this->notes,
                'status' => ReadingAssignment::STATUS_PENDING,
            ]);

            $this->sendNotifications($assignment);
            $createdCount++;
        }

        $label = $this->type === 'assignment' ? 'Assignment' : 'Recommendation';
        session()->flash('message', "{$label} sent to {$createdCount} student(s) successfully.");

        $this->showForm = false;
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $assignment = ReadingAssignment::forTeacher(auth()->id())->findOrFail($id);
        $this->editId = $assignment->id;
        $this->assignTo = 'individual';
        $this->student_id = (string) $assignment->student_id;
        $this->book_id = (string) ($assignment->book_id ?? '');
        $this->digital_asset_id = (string) ($assignment->digital_asset_id ?? '');
        $this->title = $assignment->title;
        $this->description = $assignment->description;
        $this->due_date = $assignment->due_date?->format('Y-m-d\TH:i') ?? '';
        $this->type = $assignment->type;
        $this->notes = $assignment->notes;
        $this->editing = true;
        $this->showForm = true;
    }

    public function update(): void
    {
        $this->validate();

        $assignment = ReadingAssignment::forTeacher(auth()->id())->findOrFail($this->editId);
        $assignment->update([
            'student_id' => $this->student_id,
            'book_id' => $this->book_id ?: null,
            'digital_asset_id' => $this->digital_asset_id ?: null,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date ?: null,
            'type' => $this->type,
            'notes' => $this->notes,
        ]);

        session()->flash('message', 'Updated successfully.');

        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        ReadingAssignment::forTeacher(auth()->id())->findOrFail($id)->delete();
        session()->flash('message', 'Deleted successfully.');
    }

    public function showProgress(int $id): void
    {
        $this->progressAssignmentId = $id;
    }

    public function closeProgress(): void
    {
        $this->progressAssignmentId = null;
    }

    public function getStudentsProperty()
    {
        return User::byRole('student')->active()->orderBy('name')->get();
    }

    public function getProgramsProperty()
    {
        return Program::active()->orderBy('name')->get();
    }

    public function getDepartmentsProperty()
    {
        return Department::active()->orderBy('name')->get();
    }

    public function getBooksProperty()
    {
        return Book::active()->orderBy('title')->get();
    }

    public function getDigitalAssetsProperty()
    {
        return DigitalAsset::active()->orderBy('title')->get();
    }

    public function getProgressAssignmentProperty()
    {
        if (!$this->progressAssignmentId) {
            return null;
        }

        return ReadingAssignment::forTeacher(auth()->id())
            ->with(['student', 'book', 'digitalAsset'])
            ->find($this->progressAssignmentId);
    }

    public function getProgressStatsProperty()
    {
        if (!$this->progressAssignmentId) {
            return collect();
        }

        $base = ReadingAssignment::forTeacher(auth()->id());

        $assignment = $base->find($this->progressAssignmentId);

        if (!$assignment) {
            return collect();
        }

        $query = ReadingAssignment::where('teacher_id', auth()->id())
            ->where('title', $assignment->title)
            ->where('created_at', $assignment->created_at);

        if ($assignment->program_id) {
            $query->where('program_id', $assignment->program_id);
        } elseif ($assignment->department_id) {
            $query->where('department_id', $assignment->department_id);
        }

        return $query->with('student')
            ->orderBy('student_id')
            ->get()
            ->map(function ($a) {
                return [
                    'student_name' => $a->student->name,
                    'status' => $a->status,
                    'viewed_at' => $a->viewed_at,
                    'completed_at' => $a->completed_at,
                ];
            });
    }

    public function getAssignmentsProperty()
    {
        $query = ReadingAssignment::forTeacher(auth()->id())
            ->with(['student', 'book', 'digitalAsset']);

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhereHas('student', function ($q) {
                      $q->where('name', 'like', "%{$this->search}%");
                  });
            });
        }

        return $query->latest()->paginate(15);
    }

    public function render()
    {
        return view('assignments::livewire.teacher-assignments', [
            'assignments' => $this->assignments,
            'students' => $this->students,
            'programs' => $this->programs,
            'departments' => $this->departments,
            'books' => $this->books,
            'digitalAssets' => $this->digitalAssets,
            'progressAssignment' => $this->progressAssignment,
            'progressStats' => $this->progressStats,
        ])->layout('layouts.app');
    }
}
