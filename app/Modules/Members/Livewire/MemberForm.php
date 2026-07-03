<?php

namespace App\Modules\Members\Livewire;

use App\Models\Department;
use App\Models\Program;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\MemberService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class MemberForm extends Component
{
    use WithFileUploads;

    public ?int $memberId = null;

    public string $first_name = '';

    public string $last_name = '';

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $date_of_birth = null;

    public ?string $address = null;

    public ?string $gender = null;

    public ?string $id_number = null;

    public ?string $admission_number = null;

    public ?int $department_id = null;

    public ?int $program_id = null;

    public ?string $class = null;

    public string $membership_type = 'student';

    public string $status = Member::STATUS_ACTIVE;

    public ?string $joined_at = null;

    public ?string $expires_at = null;

    public ?string $notes = null;

    public $photo = null;

    public ?string $existingPhotoUrl = null;

    public bool $isEditing = false;

    public string $mode = 'single'; // 'single' or 'bulk'

    public function mount(?int $id = null): void
    {
        if ($id) {
            abort_unless(auth()->user()->can('edit-members'), 403);
            $this->isEditing = true;
            $this->memberId = $id;
            $member = app(MemberService::class)->find($id);

            $this->first_name = $member->first_name;
            $this->last_name = $member->last_name;
            $this->email = $member->email;
            $this->phone = $member->phone;
            $this->date_of_birth = $member->date_of_birth?->format('Y-m-d');
            $this->address = $member->address;
            $this->gender = $member->gender;
            $this->id_number = $member->id_number;
            $this->admission_number = $member->admission_number;
            $this->department_id = $member->department_id;
            $this->program_id = $member->program_id;
            $this->class = $member->class;
            $this->membership_type = $member->membership_type;
            $this->status = $member->status;
            $this->joined_at = $member->joined_at?->format('Y-m-d');
            $this->expires_at = $member->expires_at?->format('Y-m-d');
            $this->notes = $member->notes;
            $this->existingPhotoUrl = $member->photo ? Storage::url($member->photo) : null;

        } else {
            abort_unless(auth()->user()->can('create-members'), 403);
            $this->joined_at ??= now()->toDateString();
            $this->expires_at ??= now()->addYear()->toDateString();
        }
    }

    public function updatedMembershipType(string $value): void
    {
        // Role is auto-assigned based on membership type in the service
    }

    public function rules(): array
    {
        $uniqueEmail = 'unique:members,email';
        $uniqueIdNumber = 'unique:members,id_number';
        $uniqueAdmission = 'unique:members,admission_number';
        if ($this->isEditing && $this->memberId) {
            $uniqueEmail .= ','.$this->memberId;
            $uniqueIdNumber .= ','.$this->memberId;
            $uniqueAdmission .= ','.$this->memberId;
        }

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $uniqueEmail],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'gender' => ['nullable', 'in:male,female,other'],
            'id_number' => ['nullable', 'string', 'max:50', $uniqueIdNumber],
            'admission_number' => ['nullable', 'string', 'max:50', $uniqueAdmission],
            'class' => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'membership_type' => ['required', 'in:student,teacher,staff,external'],
            'status' => ['required', 'in:'.implode(',', [Member::STATUS_ACTIVE, Member::STATUS_SUSPENDED, Member::STATUS_EXPIRED, Member::STATUS_INACTIVE])],
            'joined_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:joined_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];

        return $rules;
    }

    public function save(): void
    {
        $this->validate();

        $service = app(MemberService::class);
        $data = $this->getFormData();

        if ($this->photo) {
            $data['photo'] = $this->photo->store('members/photos', 'public');
        }

        if (! $this->isEditing) {
            // Always create a linked user account
            $data['create_user'] = true;

            $member = $service->registerMember($data);

            session()->flash('success', 'Member registered successfully. A login account has been created automatically.');
            $this->redirect(route('members.show', $member->id), navigate: true);
        } else {
            $member = $service->updateMember($this->memberId, $data);
            session()->flash('success', 'Member updated successfully.');
            $this->redirect(route('members.show', $member->id), navigate: true);
        }
    }

    protected function getFormData(): array
    {
        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth,
            'address' => $this->address,
            'gender' => $this->gender,
            'id_number' => $this->id_number,
            'admission_number' => $this->admission_number,
            'class' => $this->class,
            'department_id' => $this->department_id,
            'program_id' => $this->program_id,
            'membership_type' => $this->membership_type,
            'status' => $this->status,
            'joined_at' => $this->joined_at,
            'expires_at' => $this->expires_at,
            'notes' => $this->notes,
        ];

        return Arr::where($data, fn ($value) => $value !== null);
    }

    public function render()
    {
        return view('members::livewire.member-form', [
            'departments' => Department::active()->orderBy('name')->get(),
            'programs' => Program::active()->orderBy('name')->get(),
        ]);
    }
}
