<?php

namespace App\Modules\Communication\Livewire;

use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Models\MessageTemplate;
use App\Modules\Communication\Services\MessagingService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class MessageForm extends Component
{
    use WithFileUploads;

    public ?int $messageId = null;

    public string $subject = '';

    public string $body = '';

    public string $priority = 'normal';

    public string $type = 'direct';

    public ?string $scheduled_at = null;

    public array $selectedRecipients = [];

    public ?int $department_id = null;

    public ?int $program_id = null;

    public array $attachments = [];

    public ?int $template_id = null;

    public string $recipientSearch = '';

    protected function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'priority' => ['required', 'in:low,normal,high'],
            'type' => ['required', 'in:direct,group,broadcast,department,program'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'selectedRecipients' => ['required_if:type,direct,group', 'array', 'min:1'],
            'selectedRecipients.*' => ['exists:users,id'],
            'department_id' => ['required_if:type,department', 'nullable', 'exists:departments,id'],
            'program_id' => ['required_if:type,program', 'nullable', 'exists:programs,id'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    public function mount(?int $id = null): void
    {
        if ($id) {
            $message = Message::findOrFail($id);
            $this->messageId = $message->id;
            $this->subject = $message->subject;
            $this->body = $message->body;
            $this->priority = $message->priority;
            $this->type = $message->type;
            $this->scheduled_at = $message->scheduled_at?->format('Y-m-d H:i');
        }
    }

    public function updatedTemplateId(): void
    {
        if (! $this->template_id) {
            return;
        }

        $template = MessageTemplate::find($this->template_id);
        if ($template) {
            $this->subject = $template->subject;
            $this->body = $template->body;
            $this->dispatch('notify', message: 'Template applied.', type: 'info');
        }
    }

    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    /** Select all users matching the current search query (max 500) */
    public function selectAll(): void
    {
        $query = User::where('is_active', true);

        if ($this->recipientSearch) {
            $search = $this->recipientSearch;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        $ids = $query->limit(500)->pluck('id')->toArray();
        $this->selectedRecipients = $ids;

        $total = $query->count();
        if ($total > 500) {
            $this->dispatch('notify', type: 'info', message: "Selected first 500 of {$total} matching users. Refine your search for more precise selection.");
        }
    }

    /** Deselect all recipients */
    public function deselectAll(): void
    {
        $this->selectedRecipients = [];
    }

    /** Add all users with a specific role */
    public function addByRole(string $role): void
    {
        $ids = User::role($role)->where('is_active', true)->pluck('id')->toArray();
        $this->selectedRecipients = array_values(array_unique([...$this->selectedRecipients, ...$ids]));
    }

    /** Remove a single recipient by ID */
    public function removeRecipient(int $userId): void
    {
        $this->selectedRecipients = array_values(array_diff($this->selectedRecipients, [$userId]));
    }

    public function save(MessagingService $messagingService): void
    {
        $this->validate();

        $data = [
            'subject' => $this->subject,
            'body' => $this->body,
            'priority' => $this->priority,
            'type' => $this->type,
            'scheduled_at' => $this->scheduled_at,
            'recipients' => $this->selectedRecipients,
            'department_id' => $this->department_id,
            'program_id' => $this->program_id,
            'attachments' => $this->attachments,
        ];

        $messagingService->sendMessage(auth()->user(), $data);

        $this->dispatch('notify', message: 'Message sent successfully.', type: 'success');
        $this->redirect(route('communication.messages.index'), navigate: true);
    }

    public function render()
    {
        $templates = MessageTemplate::active()->orderBy('name')->get();

        $usersQuery = User::with('department')->where('is_active', true);

        if ($this->recipientSearch) {
            $search = $this->recipientSearch;
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->orderBy('name')->get(['id', 'name', 'email', 'department_id']);
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $programs = Program::orderBy('name')->get(['id', 'name']);
        $roles = Role::whereNotIn('name', ['super-admin', 'guest'])->orderBy('name')->get(['id', 'name']);

        $selectedUsers = [];
        if (! empty($this->selectedRecipients)) {
            $selectedUsers = User::with('department')
                ->whereIn('id', $this->selectedRecipients)
                ->get(['id', 'name', 'email', 'department_id'])
                ->keyBy('id');
        }

        return view('communication::livewire.message-form', [
            'templates' => $templates,
            'users' => $users,
            'departments' => $departments,
            'programs' => $programs,
            'roles' => $roles,
            'selectedUsers' => $selectedUsers,
        ]);
    }
}
