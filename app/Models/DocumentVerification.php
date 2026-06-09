<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DocumentVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'title',
        'type',
        'generated_by',
        'generated_at',
        'metadata',
        'verified_at',
        'verified_by',
        'verification_count',
        'is_revoked',
    ];

    protected $casts = [
        'metadata' => 'array',
        'generated_at' => 'datetime',
        'verified_at' => 'datetime',
        'verification_count' => 'integer',
        'is_revoked' => 'boolean',
    ];

    public static function generateDocumentId(): string
    {
        $prefix = 'DOC';
        $timestamp = now()->format('ymd');
        $random = strtoupper(Str::random(8));
        $id = "{$prefix}-{$timestamp}-{$random}";

        while (static::where('document_id', $id)->exists()) {
            $random = strtoupper(Str::random(8));
            $id = "{$prefix}-{$timestamp}-{$random}";
        }

        return $id;
    }

    public function scopeValid($query)
    {
        return $query->where('is_revoked', false);
    }

    public function markVerified(): void
    {
        $this->increment('verification_count');
        $this->verified_at = now();
        $this->save();
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'generated_by');
    }

    public function getVerificationUrlAttribute(): string
    {
        return url('/verify/document/' . $this->document_id);
    }

    public function getQrDataAttribute(): array
    {
        return [
            'document_id' => $this->document_id,
            'title' => $this->title,
            'type' => $this->type,
            'generated_at' => $this->generated_at?->format('Y-m-d H:i:s'),
            'verification_url' => $this->verification_url,
            'institution' => config('app.name'),
        ];
    }
}
