<?php

namespace App\Modules\API\Controllers;

use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Assignments\Models\ReadingAssignment;
use App\Modules\Catalog\Models\Book;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Routing\Controller;

class TeacherAssignmentController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
        protected NotificationService $notifications,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'type' => 'sometimes|string|in:assignment,recommendation',
            'status' => 'sometimes|string|in:pending,in_progress,completed,overdue,cancelled',
            'search' => 'sometimes|string|max:255',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $assignments = ReadingAssignment::with(['student', 'book', 'digitalAsset', 'program', 'department'])
            ->forTeacher(auth()->id())
            ->when($data['type'] ?? null, fn ($q) => $q->where('type', $data['type']))
            ->when($data['status'] ?? null, fn ($q) => $q->where('status', $data['status']))
            ->when($data['search'] ?? null, fn ($q) => $q->where(function ($q) use ($data) {
                $q->where('title', 'like', '%'.$data['search'].'%')
                  ->orWhereHas('student', fn ($q) => $q->where('name', 'like', '%'.$data['search'].'%'));
            }))
            ->latest()
            ->paginate($data['per_page'] ?? 20);

        $assignments->getCollection()->transform(fn ($a) => $this->format($a));

        return $this->response->paginated($assignments);
    }

    public function store(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'assign_to' => 'required|string|in:student,program,department',
            'student_id' => 'required_if:assign_to,student|integer|exists:users,id',
            'program_id' => 'required_if:assign_to,program|integer|exists:programs,id',
            'department_id' => 'required_if:assign_to,department|integer|exists:departments,id',
            'type' => 'required|string|in:assignment,recommendation',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'due_date' => 'required_if:type,assignment|date',
            'book_id' => 'nullable|integer|exists:books,id',
            'digital_asset_id' => 'nullable|integer|exists:digital_assets,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $studentIds = match ($data['assign_to']) {
            'student' => [$data['student_id']],
            'program' => User::byRole('student')->active()->where('program_id', $data['program_id'])->pluck('id')->toArray(),
            'department' => User::byRole('student')->active()->where('department_id', $data['department_id'])->pluck('id')->toArray(),
        };

        if (empty($studentIds)) {
            return $this->response->error('No students found for the selected target.', 422);
        }

        $created = [];
        foreach ($studentIds as $studentId) {
            $assignment = ReadingAssignment::create([
                'teacher_id' => auth()->id(),
                'student_id' => $studentId,
                'book_id' => $data['book_id'] ?? null,
                'digital_asset_id' => $data['digital_asset_id'] ?? null,
                'program_id' => $data['assign_to'] === 'program' ? $data['program_id'] : null,
                'department_id' => $data['assign_to'] === 'department' ? $data['department_id'] : null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'type' => $data['type'],
                'notes' => $data['notes'] ?? null,
                'status' => ReadingAssignment::STATUS_PENDING,
            ]);

            $this->notifications->send(
                $assignment->student,
                $data['type'] === 'assignment' ? 'assignment' : 'recommendation',
                $data['type'] === 'assignment' ? 'New Assignment' : 'New Recommendation',
                $data['type'] === 'assignment'
                    ? "You have a new assignment: {$assignment->title}"
                    : "New reading recommendation: {$assignment->title}",
                'book',
                null,
            );

            $created[] = $this->format($assignment);
        }

        $message = count($created) === 1
            ? 'Assignment created successfully.'
            : count($created).' assignments created successfully.';

        return $this->response->created($created, $message);
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $assignment = ReadingAssignment::with(['student', 'book', 'digitalAsset', 'program', 'department'])
            ->forTeacher(auth()->id())
            ->findOrFail($id);

        return $this->response->success($this->format($assignment));
    }

    public function update(int $id): \Illuminate\Http\JsonResponse
    {
        $assignment = ReadingAssignment::forTeacher(auth()->id())->findOrFail($id);

        $data = request()->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:10000',
            'due_date' => 'nullable|date',
            'type' => 'sometimes|string|in:assignment,recommendation',
            'book_id' => 'nullable|integer|exists:books,id',
            'digital_asset_id' => 'nullable|integer|exists:digital_assets,id',
            'notes' => 'nullable|string|max:1000',
            'status' => 'sometimes|string|in:pending,in_progress,completed,overdue,cancelled',
        ]);

        $assignment->update($data);

        return $this->response->success($this->format($assignment->fresh()), 'Assignment updated.');
    }

    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        $assignment = ReadingAssignment::forTeacher(auth()->id())->findOrFail($id);
        $assignment->delete();

        return $this->response->success(null, 'Assignment deleted.');
    }

    public function progress(int $id): \Illuminate\Http\JsonResponse
    {
        $assignment = ReadingAssignment::forTeacher(auth()->id())->findOrFail($id);

        $students = ReadingAssignment::where('teacher_id', auth()->id())
            ->where('title', $assignment->title)
            ->where(function ($q) use ($assignment) {
                $q->where('program_id', $assignment->program_id)
                  ->orWhere('department_id', $assignment->department_id)
                  ->orWhere('student_id', $assignment->student_id);
            })
            ->with('student')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'student_id' => $a->student_id,
                'student_name' => $a->student?->name ?? 'Unknown',
                'status' => $a->status,
                'viewed_at' => $a->viewed_at?->toIso8601String(),
                'completed_at' => $a->completed_at?->toIso8601String(),
                'score' => $a->score,
                'feedback' => $a->feedback,
            ]);

        return $this->response->success([
            'assignment' => $this->format($assignment),
            'students' => $students,
            'stats' => [
                'total' => $students->count(),
                'viewed' => $students->filter(fn ($s) => $s['viewed_at'] !== null)->count(),
                'completed' => $students->filter(fn ($s) => $s['completed_at'] !== null)->count(),
            ],
        ]);
    }

    protected function format(ReadingAssignment $assignment): array
    {
        return [
            'id' => $assignment->id,
            'title' => $assignment->title,
            'description' => $assignment->description,
            'type' => $assignment->type,
            'status' => $assignment->status,
            'due_at' => $assignment->due_date?->toIso8601String(),
            'student' => $assignment->student ? [
                'id' => $assignment->student->id,
                'name' => $assignment->student->name,
                'email' => $assignment->student->email,
            ] : null,
            'book' => $assignment->book ? [
                'id' => $assignment->book->id,
                'title' => $assignment->book->title,
            ] : null,
            'digital_asset' => $assignment->digitalAsset ? [
                'id' => $assignment->digitalAsset->id,
                'title' => $assignment->digitalAsset->title,
            ] : null,
            'program' => $assignment->program ? [
                'id' => $assignment->program->id,
                'name' => $assignment->program->name,
            ] : null,
            'department' => $assignment->department ? [
                'id' => $assignment->department->id,
                'name' => $assignment->department->name,
            ] : null,
            'notes' => $assignment->notes,
            'created_at' => $assignment->created_at?->toIso8601String(),
            'updated_at' => $assignment->updated_at?->toIso8601String(),
        ];
    }
}
