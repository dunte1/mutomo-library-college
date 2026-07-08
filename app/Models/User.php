<?php

namespace App\Models;

use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Models\Reservation;
use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Models\MessageRecipient;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Members\Models\Member;
use App\Modules\Subscriptions\Models\Subscription;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'department_id',
        'program_id',
        'admission_number',
        'employee_id',
        'academic_year',
        'semester',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'passport_photo',
        'notification_preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'deleted_at' => 'datetime',
            'notification_preferences' => 'array',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function member()
    {
        return $this->hasOne(Member::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function borrowRecords()
    {
        return $this->hasMany(BorrowRecord::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messageRecipients(): HasMany
    {
        return $this->hasMany(MessageRecipient::class, 'recipient_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isLibrarian(): bool
    {
        return $this->hasRole('librarian');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function isLecturer(): bool
    {
        return $this->hasRole('lecturer');
    }

    public function getBorrowLimit(): int
    {
        if ($this->hasRole('student')) {
            return 5;
        }
        if ($this->hasRole('lecturer')) {
            return 10;
        }
        if ($this->hasRole('assistant-librarian')) {
            return 10;
        }
        if ($this->hasRole('librarian')) {
            return 15;
        }
        if ($this->hasRole('admin')) {
            return 20;
        }
        if ($this->hasRole('super-admin')) {
            return 100;
        }

        return 5;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->role($role);
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        return (new \PragmaRX\Google2FA\Google2FA())->verifyKey($this->two_factor_secret, $code);
    }
}
