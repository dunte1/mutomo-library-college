<?php

namespace App\Modules\Communication\Livewire;

use App\Modules\Communication\Models\Announcement;
use Livewire\Component;
use Livewire\WithPagination;

class AnnouncementList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->authorize('manage-announcements');
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();
        $this->dispatch('notify', message: 'Announcement deleted successfully.', type: 'success');
    }

    public function render()
    {
        $announcements = Announcement::with('creator')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                ->orWhere('content', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('communication::livewire.announcement-list', [
            'announcements' => $announcements,
        ]);
    }
}
