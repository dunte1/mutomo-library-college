<?php

namespace App\Modules\Members\Models;

use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Shared\Traits\Auditable;
use App\Modules\Shared\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use Auditable, HasFactory, Searchable, SoftDeletes;

    const STATUS_ACTIVE = 'active';

    const STATUS_SUSPENDED = 'suspended';

    const STATUS_EXPIRED = 'expired';

    const STATUS_INACTIVE = 'inactive';

    const STATUS_CLEARED = 'cleared';

    const MEMBERSHIP_STUDENT = 'student';

    const MEMBERSHIP_TEACHER = 'teacher';

    const MEMBERSHIP_STAFF = 'staff';

    const MEMBERSHIP_EXTERNAL = 'external';

    protected $fillable = [
        'user_id',
        'member_id',
        'student_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'address',
        'gender',
        'id_number',
        'admission_number',
        'class',
        'blood_group',
        'department_id',
        'program_id',
        'membership_type',
        'status',
        'joined_at',
        'expires_at',
        'notes',
        'photo',
        'registered_by',
    ];

    protected $appends = [
        'full_name',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'department_id' => 'integer',
            'program_id' => 'integer',
            'registered_by' => 'integer',
            'date_of_birth' => 'date',
            'joined_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function libraryCard(): HasOne
    {
        return $this->hasOne(LibraryCard::class)->where('status', 'active');
    }

    public function libraryCards(): HasMany
    {
        return $this->hasMany(LibraryCard::class);
    }

    public function borrowRecords()
    {
        return $this->hasMany(BorrowRecord::class, 'user_id');
    }

    public function activeBorrows()
    {
        return $this->hasMany(BorrowRecord::class, 'user_id')->whereNull('returned_at');
    }

    public function overdueBorrows()
    {
        return $this->hasMany(BorrowRecord::class, 'user_id')
            ->whereNull('returned_at')->where('due_at', '<', now());
    }

    public function fines()
    {
        return $this->hasMany(Fine::class, 'user_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->whereFullText('first_name,last_name', $term)
                ->orWhereLike('member_id', $term)
                ->orWhereLike('email', $term)
                ->orWhereLike('phone', $term)
                ->orWhereLike('id_number', $term)
                ->orWhereLike('admission_number', $term);
        });
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($member) {
            if (empty($member->member_id)) {
                $last = static::withTrashed()->lockForUpdate()->max('id') ?? 0;
                $member->member_id = 'MEM-'.str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            }

            if (empty($member->student_id)) {
                $next = (static::withTrashed()->lockForUpdate()->max('id') ?? 0) + 1;
                $year = now()->format('Y');
                $member->student_id = 'OLLMCHS-'.$year.'-'.str_pad($next, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
