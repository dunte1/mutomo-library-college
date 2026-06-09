<?php

namespace App\Modules\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'subject',
        'body',
        'variables',
        'category',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function render(array $data = []): array
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($data as $key => $value) {
            $subject = str_replace("{{$key}}", $value, $subject);
            $body = str_replace("{{$key}}", $value, $body);
        }

        return ['subject' => $subject, 'body' => $body];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
