<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Models\NewsletterSubscriber;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscriberList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    protected $queryString = ['search', 'filterStatus'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);
        $subscriber->delete();
        $this->dispatch('notify', message: 'Subscriber deleted.', type: 'success');
    }

    public function exportCsv(): StreamedResponse
    {
        $subscribers = NewsletterSubscriber::query()
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'unsubscribed', fn ($q) => $q->where('is_active', false))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->streamDownload(function () use ($subscribers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Email', 'Name', 'Subscribed At', 'Status']);

            foreach ($subscribers as $s) {
                fputcsv($handle, [
                    $s->email,
                    $s->name ?? '',
                    $s->subscribed_at?->format('Y-m-d H:i:s') ?? $s->created_at->format('Y-m-d H:i:s'),
                    $s->is_active ? 'Active' : 'Unsubscribed',
                ]);
            }

            fclose($handle);
        }, 'newsletter-subscribers.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        $subscribers = NewsletterSubscriber::when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'unsubscribed', fn ($q) => $q->where('is_active', false))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('settings::livewire.newsletter-subscriber-list', [
            'subscribers' => $subscribers,
        ]);
    }
}
