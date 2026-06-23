<?php

namespace App\Modules\DigitalLibrary\Services;

use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Models\ReadingHistory;
use App\Traits\Auditable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DigitalLibraryService
{
    use Auditable;

    public function upload(UploadedFile $file, array $data): DigitalAsset
    {
        $fileType = $this->classifyFileType($file);
        $extension = $file->getClientOriginalExtension();
        $slug = Str::slug($data['title']).'-'.Str::random(6);

        $path = $file->store("digital-library/{$fileType}", 'public');

        $asset = DigitalAsset::create([
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'file_path' => $path,
            'file_type' => $fileType,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_extension' => $extension,
            'cover_image' => $data['cover_image'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'author' => $data['author'] ?? null,
            'publisher' => $data['publisher'] ?? null,
            'isbn' => $data['isbn'] ?? null,
            'publication_year' => $data['publication_year'] ?? null,
            'language' => $data['language'] ?? 'en',
            'keywords' => isset($data['keywords']) ? explode(',', $data['keywords']) : null,
            'access_level' => $data['access_level'] ?? 'restricted',
            'allow_download' => $data['allow_download'] ?? true,
            'allow_printing' => $data['allow_printing'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'uploaded_by' => auth()->id(),
        ]);

        $this->logActivity('uploaded', "Uploaded digital asset: {$asset->title}", $asset);

        return $asset;
    }

    public function delete(DigitalAsset $asset): void
    {
        Storage::disk('public')->delete($asset->file_path);

        if ($asset->cover_image) {
            Storage::disk('public')->delete($asset->cover_image);
        }

        $title = $asset->title;
        $asset->delete();

        $this->logActivity('deleted', "Deleted digital asset: {$title}");
    }

    public function trackView(DigitalAsset $asset): void
    {
        $asset->incrementViews();

        if (auth()->check()) {
            ReadingHistory::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'trackable_type' => DigitalAsset::class,
                    'trackable_id' => $asset->id,
                ],
                [
                    'digital_asset_id' => $asset->id,
                    'started_at' => now(),
                ]
            );
        }
    }

    public function trackProgress(int $assetId, int $progress, ?int $lastPage = null): void
    {
        if (! auth()->check()) {
            return;
        }

        ReadingHistory::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'digital_asset_id' => $assetId,
            ],
            [
                'progress' => $progress,
                'last_page' => $lastPage,
                'completed_at' => $progress >= 100 ? now() : null,
                'started_at' => now(),
            ]
        );
    }

    public function classifyFileType(UploadedFile $file): string
    {
        $mime = $file->getMimeType();
        $ext = strtolower($file->getClientOriginalExtension());

        return match (true) {
            in_array($mime, ['application/pdf']) && $ext === 'pdf' => 'pdf',
            in_array($mime, ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword']) => 'lecture_note',
            in_array($mime, ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.ms-powerpoint']) => 'presentation',
            in_array($mime, ['video/mp4', 'video/x-msvideo', 'video/quicktime', 'video/x-matroska']) => 'video',
            in_array($mime, ['audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/mp4']) => 'audio',
            in_array($mime, ['text/csv', 'application/json', 'application/xml']) => 'dataset',
            str_contains($mime, 'text/') || str_contains($ext, 'epub') => 'ebook',
            default => 'pdf',
        };
    }

    public function search(string $query, array $filters = []): mixed
    {
        $assets = DigitalAsset::active();

        if (! empty($query)) {
            $assets->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('author', 'like', "%{$query}%")
                    ->orWhere('keywords', 'like', "%{$query}%");
            });
        }

        if (! empty($filters['type'])) {
            $assets->ofType($filters['type']);
        }

        if (! empty($filters['category_id'])) {
            $assets->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['access_level'])) {
            $assets->accessible($filters['access_level']);
        } else {
            $assets->accessible();
        }

        $allowedSortFields = ['title', 'created_at', 'file_size', 'publication_year', 'language', 'author'];
        $sortField = in_array($filters['sort'] ?? 'created_at', $allowedSortFields) ? $filters['sort'] : 'created_at';
        $sortDir = in_array(strtolower($filters['direction'] ?? 'desc'), ['asc', 'desc']) ? strtolower($filters['direction']) : 'desc';
        $assets->orderBy($sortField, $sortDir);

        return $assets->paginate($filters['per_page'] ?? 12);
    }
}
