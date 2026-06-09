<?php

namespace App\Modules\DigitalLibrary\Models;

use App\Modules\Catalog\Models\Book;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Citation extends Model
{
    protected $fillable = ['digital_asset_id', 'book_id', 'citation_text', 'style'];

    public function digitalAsset(): BelongsTo
    {
        return $this->belongsTo(DigitalAsset::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public static function supportedStyles(): array
    {
        return ['apa', 'mla', 'chicago', 'harvard', 'vancouver', 'ieee'];
    }

    public static function generateAPA(string $author, int $year, string $title, ?string $publisher, ?string $url): string
    {
        return "{$author}. ({$year}). *{$title}*." . ($publisher ? " {$publisher}." : '') . ($url ? " {$url}" : '');
    }

    public static function generateMLA(string $author, string $title, ?string $publisher, int $year): string
    {
        return "{$author}. *{$title}*." . ($publisher ? " {$publisher}, " : ' ') . "{$year}.";
    }

    public static function generateChicago(string $author, string $title, ?string $publisher, int $year): string
    {
        return "{$author}. *{$title}*. " . ($publisher ? "{$publisher}, " : '') . "{$year}.";
    }
}
