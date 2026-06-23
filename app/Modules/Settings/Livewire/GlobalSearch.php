<?php

namespace App\Modules\Settings\Livewire;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\Members\Models\Member;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';

    public bool $show = false;

    public array $results = [];

    public string $activeGroup = 'all';

    protected $listeners = ['openGlobalSearch' => 'open'];

    public function open(): void
    {
        $this->show = true;
        $this->query = '';
        $this->results = [];
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function updatedQuery(): void
    {
        if (strlen($this->query) < 2) {
            $this->results = [];

            return;
        }

        $q = $this->query;

        $books = Book::active()
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('isbn', 'like', "%{$q}%");
            })
            ->take(5)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'type' => 'book',
                'title' => $b->title,
                'subtitle' => $b->isbn ?? '',
                'url' => route('catalog.books.show', $b->id),
                'icon' => 'book',
            ]);

        $members = Member::where(function ($query) use ($q) {
            $query->where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")
                ->orWhere('member_id', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('admission_number', 'like', "%{$q}%");
        })
            ->take(5)
            ->get()
            ->map(fn ($m) => [
            'id' => $m->id,
            'type' => 'member',
            'title' => $m->full_name,
            'subtitle' => $m->member_id.' — '.ucfirst($m->membership_type),
            'url' => route('members.show', $m->id),
            'icon' => 'user',
        ]);

        $assets = DigitalAsset::active()
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('author', 'like', "%{$q}%")
                    ->orWhere('keywords', 'like', "%{$q}%");
            })
            ->take(5)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'type' => 'asset',
                'title' => $a->title,
                'subtitle' => ucfirst($a->file_type).' — '.($a->author ?? ''),
                'url' => route('digital-library.show', $a->id),
                'icon' => 'document',
            ]);

        $users = User::where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('admission_number', 'like', "%{$q}%")
                ->orWhere('employee_id', 'like', "%{$q}%");
        })
            ->take(3)
            ->get()
            ->map(fn ($u) => [
            'id' => $u->id,
            'type' => 'user',
            'title' => $u->name,
            'subtitle' => $u->email,
            'url' => route('settings.users.edit', $u->id),
            'icon' => 'user',
        ]);

        $this->results = [
            'books' => $books,
            'members' => $members,
            'assets' => $assets,
            'users' => $users,
        ];
    }

    public function render()
    {
        return view('settings::livewire.global-search');
    }
}
