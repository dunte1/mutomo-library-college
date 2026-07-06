<?php

namespace App\Modules\Members\Services;

use App\Mail\WelcomeCredentials;
use App\Models\User;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Repositories\MemberRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\PermissionRegistrar;

class MemberService
{
    public function __construct(
        protected MemberRepository $memberRepository,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->memberRepository->paginate($perPage);
    }

    public function find(int $id): Member
    {
        return $this->memberRepository->findOrFail($id);
    }

    public function findByMemberId(string $memberId): ?Member
    {
        return $this->memberRepository->findByMemberId($memberId);
    }

    /**
     * Get the default role name for a membership type.
     */
    public function getDefaultRoleForMembershipType(string $membershipType): string
    {
        return match ($membershipType) {
            Member::MEMBERSHIP_STUDENT => 'student',
            Member::MEMBERSHIP_TEACHER => 'lecturer',
            Member::MEMBERSHIP_STAFF => 'staff',
            default => 'guest',
        };
    }

    /**
     * Register a new member, optionally creating a linked User account.
     *
     * @param  array  $data  Member data, plus optional keys:
     *                       - create_user (bool)
     *                       - password (string)
     *                       - role (string|array)
     */
    public function registerMember(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $createUser = ! empty($data['create_user']);
            unset($data['create_user']);

            $password = $data['password'] ?? null;
            unset($data['password']);

            $role = $data['role'] ?? null;
            unset($data['role']);

            $data['registered_by'] = Auth::id();
            $data['joined_at'] ??= now()->toDateString();
            $data['expires_at'] ??= now()->addYear()->toDateString();

            // If creating a user, check for duplicate email first
            $plainPassword = null;
            if ($createUser && ! empty($data['email'])) {
                $existingUser = User::where('email', $data['email'])->first();
                if ($existingUser) {
                    // Link existing user to this member
                    $data['user_id'] = $existingUser->id;
                } else {
                    // Create new user
                    $plainPassword = $password ?? substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
                    $userData = [
                        'name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
                        'email' => $data['email'],
                        'password' => Hash::make($plainPassword),
                        'phone' => $data['phone'] ?? null,
                        'is_active' => true,
                    ];

                    if (! empty($data['admission_number'])) {
                        $userData['admission_number'] = $data['admission_number'];
                    }
                    if (! empty($data['department_id'])) {
                        $userData['department_id'] = $data['department_id'];
                    }
                    if (! empty($data['program_id'])) {
                        $userData['program_id'] = $data['program_id'];
                    }

                    $user = User::create($userData);

                    // Assign role
                    $roleName = $role ?? $this->getDefaultRoleForMembershipType($data['membership_type'] ?? 'student');
                    $roleNames = is_array($roleName) ? $roleName : [$roleName];
                    $user->assignRole($roleNames);

                    // Clear permission cache so the new role is active immediately
                    app(PermissionRegistrar::class)->forgetCachedPermissions();

                    $data['user_id'] = $user->id;
                }
            }

            $member = $this->memberRepository->create($data);

            // Auto-generate library card for new member
            try {
                app(LibraryCardService::class)->autoIssueCard($member);
            } catch (\Throwable $e) {
                report($e);
            }

            // Send welcome email with password reset link
            if ($createUser && ! empty($member->email) && $member->user_id) {
                try {
                    $member->load('user');
                    if ($member->user) {
                        Mail::to($member->email)->send(new WelcomeCredentials($member->user));
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            // Load user relationship for response
            if ($createUser && $member->user_id) {
                $member->load('user');
            }

            return $member;
        });
    }

    public function updateMember(Member $member, array $data): Member
    {
        return DB::transaction(function () use ($member, $data) {
            return $this->memberRepository->updateMember($member, $data);
        });
    }

    public function deleteMember(Member $member): bool
    {
        return DB::transaction(function () use ($member) {
            return $this->memberRepository->deleteMember($member);
        });
    }

    public function suspendMember(Member $member, string $reason): void
    {
        DB::transaction(function () use ($member, $reason) {
            $member->update([
                'status' => Member::STATUS_SUSPENDED,
                'notes' => $member->notes
                    ? $member->notes."\n[Suspended: {$reason}]"
                    : "[Suspended: {$reason}]",
            ]);
        });
    }

    public function activateMember(Member $member): void
    {
        DB::transaction(function () use ($member) {
            $member->update(['status' => Member::STATUS_ACTIVE]);
        });
    }

    public function clearMember(Member $member, string $notes = ''): void
    {
        DB::transaction(function () use ($member, $notes) {
            $outstandingFines = $member->fines()
                ->where('status', 'pending')
                ->selectRaw('COALESCE(SUM(amount), 0) - COALESCE(SUM(paid_amount), 0) - COALESCE(SUM(waived_amount), 0) as outstanding')
                ->value('outstanding');

            $activeBorrows = $member->borrowRecords()
                ->whereIn('status', ['active', 'overdue'])
                ->count();

            if ($outstandingFines > 0) {
                throw new \RuntimeException("Cannot clear member with outstanding fines of KES {$outstandingFines}.");
            }

            if ($activeBorrows > 0) {
                throw new \RuntimeException("Cannot clear member with {$activeBorrows} active borrow(s).");
            }

            $member->update([
                'status' => Member::STATUS_CLEARED,
                'notes' => $member->notes
                    ? $member->notes."\n[Cleared: {$notes}]"
                    : "[Cleared: {$notes}]",
            ]);

            activity()
                ->performedOn($member)
                ->causedBy(auth()->user())
                ->log("Member {$member->full_name} cleared. {$notes}");
        });
    }

    public function renewMembership(Member $member, ?int $months = 12): void
    {
        DB::transaction(function () use ($member, $months) {
            $member->update([
                'status' => Member::STATUS_ACTIVE,
                'expires_at' => now()->addMonths($months)->toDateString(),
            ]);
        });
    }

    public function getMemberStats(): array
    {
        $stats = Member::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = '".Member::STATUS_ACTIVE."' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = '".Member::STATUS_SUSPENDED."' THEN 1 ELSE 0 END) as suspended,
            SUM(CASE WHEN status = '".Member::STATUS_EXPIRED."' THEN 1 ELSE 0 END) as expired,
            SUM(CASE WHEN status = '".Member::STATUS_INACTIVE."' THEN 1 ELSE 0 END) as inactive,
            SUM(CASE WHEN status = '".Member::STATUS_CLEARED."' THEN 1 ELSE 0 END) as cleared
        ")->first();

        $newThisMonth = Member::whereMonth('joined_at', now()->month)
            ->whereYear('joined_at', now()->year)
            ->count();

        return [
            'total' => (int) $stats->total,
            'active' => (int) $stats->active,
            'suspended' => (int) $stats->suspended,
            'expired' => (int) $stats->expired,
            'inactive' => (int) $stats->inactive,
            'cleared' => (int) $stats->cleared,
            'newThisMonth' => $newThisMonth,
        ];
    }

    public function searchMembers(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->memberRepository->search($query, $perPage);
    }

    public function searchWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Member::with(['registeredBy', 'user', 'department', 'program']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('member_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['membership_type'])) {
            $query->where('membership_type', $filters['membership_type']);
        }

        $allowedSortFields = ['member_id', 'first_name', 'last_name', 'email', 'phone', 'status', 'membership_type', 'joined_at', 'expires_at', 'created_at'];
        $sortField = in_array($filters['sort'] ?? '', $allowedSortFields) ? $filters['sort'] : 'created_at';
        $sortDir = in_array(strtolower($filters['direction'] ?? ''), ['asc', 'desc']) ? strtolower($filters['direction']) : 'desc';
        $query->orderBy($sortField, $sortDir);

        return $query->paginate($perPage);
    }
}
