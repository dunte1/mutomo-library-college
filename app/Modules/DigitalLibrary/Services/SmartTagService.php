<?php

namespace App\Modules\DigitalLibrary\Services;

use App\Modules\Catalog\Models\Book;
use App\Modules\DigitalLibrary\Models\DigitalAsset;

class SmartTagService
{
    public function generateTags(Book|DigitalAsset $source): array
    {
        $tags = [];

        if ($source->title) {
            $words = explode(' ', $source->title);
            $tags = array_merge($tags, array_filter($words, fn ($w) => strlen($w) > 3));
        }

        if ($source instanceof Book) {
            $tags = array_merge($tags, $source->categories->pluck('name')->toArray());
            $tags = array_merge($tags, $source->subjects->pluck('name')->toArray());
            $tags[] = $source->publisher;

            if ($source->isbn) {
                $tags[] = 'isbn:'.$source->isbn;
            }
        }

        if ($source instanceof DigitalAsset && $source->keywords) {
            $tags = array_merge($tags, $source->keywords);
        }

        $tags = array_map('strtolower', $tags);
        $tags = array_map('trim', $tags);
        $tags = array_unique(array_filter($tags));
        $tags = array_slice($tags, 0, 20);

        sort($tags);

        return $tags;
    }

    public function findRelatedResources(Book $book, int $limit = 5): array
    {
        $bookTags = $this->generateTags($book);

        $related = DigitalAsset::active()
            ->where(function ($q) use ($bookTags) {
                foreach ($bookTags as $tag) {
                    $q->orWhere('keywords', 'like', "%{$tag}%")
                        ->orWhere('title', 'like', "%{$tag}%")
                        ->orWhere('description', 'like', "%{$tag}%");
                }
            })
            ->limit($limit)
            ->get();

        return $related->map(fn ($asset) => [
            'digital_asset_id' => $asset->id,
            'title' => $asset->title,
            'type' => $asset->file_type,
            'relevance' => $this->calculateRelevance($book, $asset),
        ])->sortByDesc('relevance')->values()->toArray();
    }

    private function calculateRelevance(Book $book, DigitalAsset $asset): float
    {
        $score = 0.0;

        $commonWords = array_intersect(
            explode(' ', strtolower($book->title)),
            explode(' ', strtolower($asset->title))
        );
        $score += count($commonWords) * 0.2;

        if ($asset->author && $book->author && str_contains(strtolower($asset->author), strtolower($book->author))) {
            $score += 0.3;
        }

        $bookCategories = $book->categories->pluck('id')->toArray();
        if ($asset->category_id && in_array($asset->category_id, $bookCategories)) {
            $score += 0.25;
        }

        return min($score, 1.0);
    }
}
