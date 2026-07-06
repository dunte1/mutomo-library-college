<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\Assignments\Models\ReadingAssignment;
use Illuminate\Routing\Controller;

class AssignmentController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $assignments = ReadingAssignment::with('teacher')
            ->where('student_id', auth()->id())
            ->latest('due_date')
            ->paginate(20);

        $assignments->getCollection()->transform(fn ($a) => $this->format($a));

        return $this->response->paginated($assignments);
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $assignment = ReadingAssignment::with('teacher')
            ->where('student_id', auth()->id())
            ->findOrFail($id);

        $assignment->markAsViewed();

        return $this->response->success($this->format($assignment));
    }

    public function submit(int $id): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'submission_text' => 'nullable|string|max:10000',
            'attachment_url' => 'nullable|url|max:2048',
        ]);

        $assignment = ReadingAssignment::where('student_id', auth()->id())
            ->whereIn('status', ['pending', 'in_progress'])
            ->findOrFail($id);

        $assignment->update([
            'submission_text' => $data['submission_text'] ?? $assignment->submission_text,
            'attachment_url' => $data['attachment_url'] ?? $assignment->attachment_url,
            'status' => ReadingAssignment::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return $this->response->success($this->format($assignment->fresh()), 'Assignment submitted successfully.');
    }

    protected function format(ReadingAssignment $assignment): array
    {
        return [
            'id' => $assignment->id,
            'title' => $assignment->title,
            'subject' => $assignment->subject,
            'description' => $assignment->description,
            'attachment_url' => $assignment->attachment_url,
            'due_at' => $assignment->due_date?->toIso8601String(),
            'status' => $assignment->status,
            'teacher' => $assignment->teacher ? [
                'name' => $assignment->teacher->name,
            ] : null,
            'submitted_at' => $assignment->completed_at?->toIso8601String(),
            'feedback' => $assignment->feedback ?? $assignment->notes,
            'score' => $assignment->score,
            'created_at' => $assignment->created_at?->toIso8601String(),
        ];
    }
}
