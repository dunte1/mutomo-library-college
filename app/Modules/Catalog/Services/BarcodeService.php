<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\BookCopy;
use Illuminate\Support\Str;

class BarcodeService
{
    public function generate(): string
    {
        $prefix = 'OLLMCHS';
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(Str::random(4));
        $barcode = "{$prefix}-{$timestamp}-{$random}";

        while (BookCopy::where('barcode', $barcode)->exists()) {
            $random = strtoupper(Str::random(4));
            $barcode = "{$prefix}-{$timestamp}-{$random}";
        }

        return $barcode;
    }

    public function generateQRData(BookCopy $copy): array
    {
        return [
            'type' => 'book_copy',
            'barcode' => $copy->barcode,
            'book_id' => $copy->book_id,
            'title' => $copy->book->title,
            'url' => route('catalog.books.show', $copy->book_id),
        ];
    }
}
