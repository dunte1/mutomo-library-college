<?php

namespace App\Modules\Communication\Livewire;

use App\Modules\Communication\Models\Message;
use Livewire\Component;
use Livewire\WithPagination;

class MessageLogList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $type = '';

    public string $sort = 'created_at';

    public string $direction = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => ''],
        'sort' => ['except' => 'created_at'],
        'direction' => ['except' => 'desc'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'type', 'sort', 'direction']);
    }

    public function render()
    {
        $query = Message::with(['sender', 'recipients']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('subject', 'like', "%{$this->search}%")
                    ->orWhere('body', 'like', "%{$this->search}%");
            });
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        $messages = $query->orderBy($this->sort, $this->direction)->paginate(15);

        $stats = [
            'total' => Message::count(),
            'broadcasts' => Message::where('type', Message::TYPE_BROADCAST)->count(),
            'direct' => Message::where('type', 'direct')->count(),
        ];

        return view('communication::livewire.message-log-list', [
            'messages' => $messages,
            'stats' => $stats,
        ]);
    }
}
