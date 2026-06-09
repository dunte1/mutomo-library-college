<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Models\AuthCarouselImage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class AuthCarouselSettings extends Component
{
    use WithFileUploads;

    public $images = [];
    public $newImage;
    public $editId = null;
    public $editTitle = '';
    public $editSubtitle = '';
    public bool $saved = false;

    public function mount(): void
    {
        $this->loadImages();
    }

    public function loadImages(): void
    {
        $this->images = AuthCarouselImage::ordered()->get()->toArray();
    }

    public function add(): void
    {
        $this->validate([
            'newImage' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $path = $this->newImage->store('auth-carousel', 'public');

        AuthCarouselImage::create([
            'image_path' => $path,
            'sort_order' => AuthCarouselImage::max('sort_order') + 1,
        ]);

        $this->newImage = null;
        $this->loadImages();
        $this->saved = true;
    }

    public function edit($id): void
    {
        $image = AuthCarouselImage::findOrFail($id);
        $this->editId = $image->id;
        $this->editTitle = $image->title;
        $this->editSubtitle = $image->subtitle;
    }

    public function update(): void
    {
        $this->validate([
            'editTitle' => 'nullable|string|max:255',
            'editSubtitle' => 'nullable|string|max:255',
        ]);

        $image = AuthCarouselImage::findOrFail($this->editId);
        $image->update([
            'title' => $this->editTitle,
            'subtitle' => $this->editSubtitle,
        ]);

        $this->cancelEdit();
        $this->loadImages();
        $this->saved = true;
    }

    public function cancelEdit(): void
    {
        $this->editId = null;
        $this->editTitle = '';
        $this->editSubtitle = '';
    }

    public function toggleActive($id): void
    {
        $image = AuthCarouselImage::findOrFail($id);
        $image->update(['is_active' => !$image->is_active]);
        $this->loadImages();
    }

    public function moveUp($id): void
    {
        $image = AuthCarouselImage::findOrFail($id);
        $prev = AuthCarouselImage::where('sort_order', '<', $image->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($prev) {
            $temp = $image->sort_order;
            $image->update(['sort_order' => $prev->sort_order]);
            $prev->update(['sort_order' => $temp]);
        }

        $this->loadImages();
    }

    public function moveDown($id): void
    {
        $image = AuthCarouselImage::findOrFail($id);
        $next = AuthCarouselImage::where('sort_order', '>', $image->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($next) {
            $temp = $image->sort_order;
            $image->update(['sort_order' => $next->sort_order]);
            $next->update(['sort_order' => $temp]);
        }

        $this->loadImages();
    }

    public function delete($id): void
    {
        $image = AuthCarouselImage::findOrFail($id);
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
        $this->loadImages();
        $this->saved = true;
    }

    public function render()
    {
        return view('settings::livewire.auth-carousel-settings');
    }
}
