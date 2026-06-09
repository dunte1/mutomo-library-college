<?php

namespace App\Modules\Assignments\Models;

use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingAssignment extends Model
{
    protected $fillable = [
        'teacher_id', 'student_id', 'book_id', 'digital_asset_id',
        'program_id', 'department_id',
        'title', 'description', 'due_date', 'status', 'type',
        'notes', 'completed_at', 'viewed_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_RECOMMENDATION = 'recommendation';

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function digitalAsset(): BelongsTo
    {
        return $this->belongsTo(DigitalAsset::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeAssignments($query)
    {
        return $query->where('type', self::TYPE_ASSIGNMENT);
    }

    public function scopeRecommendations($query)
    {
        return $query->where('type', self::TYPE_RECOMMENDATION);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeUnviewed($query)
    {
        return $query->whereNull('viewed_at');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function markAsViewed(): void
    {
        if (is_null($this->viewed_at)) {
            $this->update(['viewed_at' => now()]);
        }
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_ASSIGNMENT => 'Assignment',
            self::TYPE_RECOMMENDATION => 'Recommendation',
        ];
    }
}
