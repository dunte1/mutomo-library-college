<?php

namespace App\Modules\DigitalLibrary\Models;

use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalAsset extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'title', 'slug', 'description', 'file_path', 'file_type', 'mime_type',
        'file_size', 'file_extension', 'cover_image', 'category_id',
        'author', 'publisher', 'isbn', 'publication_year', 'language',
        'keywords', 'access_level', 'allow_download', 'allow_printing',
        'times_downloaded', 'times_viewed', 'is_active', 'is_featured', 'uploaded_by',
        'book_id',
    ];

    protected $casts = [
        'keywords' => 'array',
        'allow_download' => 'boolean',
        'allow_printing' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'file_size' => 'integer',
        'times_downloaded' => 'integer',
        'times_viewed' => 'integer',
        'publication_year' => 'integer',
    ];

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DigitalAssetCategory::class, 'category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Book::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('file_type', $type);
    }

    public function scopeAccessible($query, ?string $level = null)
    {
        if ($level) {
            return $query->where('access_level', $level);
        }
        return $query->whereIn('access_level', ['public', 'restricted']);
    }

    public static function typeOptions(): array
    {
        return ['ebook', 'pdf', 'lecture_note', 'journal', 'research_paper', 'video', 'audio', 'presentation', 'dataset'];
    }

    public static function accessLevelOptions(): array
    {
        return ['public' => 'Public', 'restricted' => 'Restricted', 'private' => 'Private'];
    }

    public function incrementDownloads(): void
    {
        $this->increment('times_downloaded');
    }

    public function incrementViews(): void
    {
        $this->increment('times_viewed');
    }
}
