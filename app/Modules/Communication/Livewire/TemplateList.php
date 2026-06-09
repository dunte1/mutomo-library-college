<?php

namespace App\Modules\Communication\Livewire;

use App\Modules\Communication\Models\MessageTemplate;
use Livewire\Component;
use Livewire\WithPagination;

class TemplateList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';

    protected $queryString = ['search', 'categoryFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $template = MessageTemplate::findOrFail($id);
        $template->delete();

        activity()
            ->performedOn($template)
            ->causedBy(auth()->user())
            ->log("Message template deleted: {$template->name}");

        $this->dispatch('notify', message: 'Template deleted.', type: 'success');
    }

    public function toggleActive(int $id): void
    {
        $template = MessageTemplate::findOrFail($id);
        $template->update(['is_active' => !$template->is_active]);

        $this->dispatch('notify', message: 'Template updated.', type: 'success');
    }

    public function render()
    {
        $templates = MessageTemplate::with('creator')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('subject', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, fn($q) => $q->where('category', $this->categoryFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        $categories = MessageTemplate::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('communication::livewire.template-list', [
            'templates' => $templates,
            'categories' => $categories,
        ]);
    }
}
