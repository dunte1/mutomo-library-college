<?php

namespace App\Modules\DigitalLibrary\Services;

use App\Modules\Catalog\Models\Book;
use App\Modules\DigitalLibrary\Models\Citation;
use App\Modules\DigitalLibrary\Models\DigitalAsset;

class CitationService
{
    public function generateCitation(Book|DigitalAsset $source, string $style = 'apa'): string
    {
        $author = $source->author ?? 'Unknown';
        $year = $source->publication_year ?? now()->year;
        $title = $source->title;
        $publisher = $source->publisher ?? null;

        $citation = match ($style) {
            'apa' => Citation::generateAPA($author, (int)$year, $title, $publisher, null),
            'mla' => Citation::generateMLA($author, $title, $publisher, (int)$year),
            'chicago' => Citation::generateChicago($author, $title, $publisher, (int)$year),
            default => Citation::generateAPA($author, (int)$year, $title, $publisher, null),
        };

        Citation::create([
            'digital_asset_id' => $source instanceof DigitalAsset ? $source->id : null,
            'book_id' => $source instanceof Book ? $source->id : null,
            'citation_text' => $citation,
            'style' => $style,
        ]);

        return $citation;
    }

    public function getSupportedStyles(): array
    {
        return Citation::supportedStyles();
    }
}
