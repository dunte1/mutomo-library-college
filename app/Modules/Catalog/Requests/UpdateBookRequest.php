<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit-books');
    }

    public function rules(): array
    {
        return [
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($this->route('id'))],
            'title' => 'sometimes|required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'language' => 'nullable|string|max:10',
            'pages' => 'nullable|integer|min:1',
            'publication_year' => 'nullable|integer|min:1000|max:'.date('Y'),
            'edition' => 'nullable|string|max:50',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'publisher_id' => 'nullable|exists:publishers,id',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'nullable|numeric|min:0',
            'authors' => 'nullable|array',
            'authors.*' => 'exists:authors,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ];
    }
}
