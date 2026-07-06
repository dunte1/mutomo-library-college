<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Models\DigitalAssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class DigitalAssetFactory extends Factory
{
    protected $model = DigitalAsset::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);
        $fileType = fake()->randomElement(['pdf', 'lecture_note', 'presentation', 'video', 'audio']);

        return [
            'title' => ucfirst($title),
            'slug' => \Illuminate\Support\Str::slug($title).'-'.fake()->unique()->numerify('####'),
            'description' => fake()->paragraph(),
            'file_path' => "digital-library/{$fileType}/".fake()->uuid().".{$fileType === 'lecture_note' ? 'docx' : $fileType}",
            'file_type' => $fileType,
            'mime_type' => match ($fileType) {
                'pdf' => 'application/pdf',
                'lecture_note' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'presentation' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'video' => 'video/mp4',
                'audio' => 'audio/mpeg',
                default => 'application/pdf',
            },
            'file_size' => fake()->numberBetween(100000, 50000000),
            'file_extension' => $fileType === 'lecture_note' ? 'docx' : $fileType,
            'category_id' => DigitalAssetCategory::factory(),
            'author' => fake()->name(),
            'publication_year' => fake()->numberBetween(2000, 2026),
            'language' => 'en',
            'access_level' => 'restricted',
            'allow_download' => true,
            'is_active' => true,
            'uploaded_by' => User::factory(),
        ];
    }
}
