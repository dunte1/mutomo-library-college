<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-books');
    }

    public function rules(): array
    {
        return [
            'isbn' => 'nullable|string|max:20|unique:books,isbn',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'language' => 'nullable|string|max:10',
            'pages' => 'nullable|integer|min:1',
            'publication_year' => 'nullable|integer|min:1000|max:'.date('Y'),
            'edition' => 'nullable|string|max:50',
            'volume' => 'nullable|string|max:50',
            'series' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'condition' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:20',
            'publisher_id' => 'nullable|exists:publishers,id',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'nullable|numeric|min:0',
            'dewey_decimal' => 'nullable|string|max:50',
            'lc_classification' => 'nullable|string|max:50',
            'tags' => 'nullable|array',
            'authors' => 'nullable|array',
            'authors.*' => 'exists:authors,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'copies_count' => 'nullable|integer|min:0|max:100',
            'shelf_location' => 'nullable|string|max:100',
        ];
    }
}
