<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Models\DigitalAssetCategory;
use App\Modules\DigitalLibrary\Models\ReadingHistory;
use App\Modules\DigitalLibrary\Services\DigitalLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DigitalLibraryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DigitalLibraryService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->service = app(DigitalLibraryService::class);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_classify_file_type_returns_pdf(): void
    {
        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $type = $this->service->classifyFileType($file);

        $this->assertSame('pdf', $type);
    }

    public function test_classify_file_type_returns_lecture_note_for_docx(): void
    {
        $file = UploadedFile::fake()->create('notes.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $type = $this->service->classifyFileType($file);

        $this->assertSame('lecture_note', $type);
    }

    public function test_classify_file_type_returns_presentation_for_pptx(): void
    {
        $file = UploadedFile::fake()->create('slides.pptx', 100, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
        $type = $this->service->classifyFileType($file);

        $this->assertSame('presentation', $type);
    }

    public function test_classify_file_type_returns_video_for_mp4(): void
    {
        $file = UploadedFile::fake()->create('video.mp4', 100, 'video/mp4');
        $type = $this->service->classifyFileType($file);

        $this->assertSame('video', $type);
    }

    public function test_classify_file_type_returns_audio_for_mp3(): void
    {
        $file = UploadedFile::fake()->create('audio.mp3', 100, 'audio/mpeg');
        $type = $this->service->classifyFileType($file);

        $this->assertSame('audio', $type);
    }

    public function test_classify_file_type_defaults_to_pdf_for_unknown(): void
    {
        $file = UploadedFile::fake()->create('unknown.xyz', 100, 'application/octet-stream');
        $type = $this->service->classifyFileType($file);

        $this->assertSame('pdf', $type);
    }

    public function test_upload_creates_digital_asset(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('thesis.pdf', 500, 'application/pdf');

        $asset = $this->service->upload($file, [
            'title' => 'Test Thesis',
            'description' => 'A test thesis',
            'access_level' => 'public',
        ]);

        $this->assertInstanceOf(DigitalAsset::class, $asset);
        $this->assertSame('Test Thesis', $asset->title);
        $this->assertSame('pdf', $asset->file_type);
        $this->assertSame($this->user->id, $asset->uploaded_by);
        $this->assertTrue($asset->is_active);

        Storage::disk('public')->assertExists($asset->file_path);
    }

    public function test_upload_stores_file_in_correct_directory(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('notes.docx', 200, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $asset = $this->service->upload($file, [
            'title' => 'Lecture Notes',
            'access_level' => 'restricted',
        ]);

        $this->assertStringStartsWith('digital-library/lecture_note/', $asset->file_path);
        Storage::disk('public')->assertExists($asset->file_path);
    }

    public function test_delete_removes_asset_and_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('delete-me.pdf', 100, 'application/pdf');
        $asset = $this->service->upload($file, ['title' => 'Delete Me']);

        $filePath = $asset->file_path;

        $this->service->delete($asset);

        Storage::disk('public')->assertMissing($filePath);
        $this->assertSoftDeleted('digital_assets', ['id' => $asset->id]);
    }

    public function test_track_view_increments_view_count(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('viewed.pdf', 100, 'application/pdf');
        $asset = $this->service->upload($file, ['title' => 'Viewed Asset']);

        $this->service->trackView($asset);

        $this->assertSame(1, $asset->fresh()->times_viewed);

        $this->assertDatabaseHas('reading_histories', [
            'user_id' => $this->user->id,
            'trackable_type' => DigitalAsset::class,
            'trackable_id' => $asset->id,
        ]);
    }

    public function test_track_progress_creates_or_updates_history(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('progress.pdf', 100, 'application/pdf');
        $asset = $this->service->upload($file, ['title' => 'Progress Tracking']);

        $this->service->trackProgress($asset->id, 50, 25);

        $this->assertDatabaseHas('reading_histories', [
            'user_id' => $this->user->id,
            'digital_asset_id' => $asset->id,
            'progress' => 50,
            'last_page' => 25,
            'completed_at' => null,
        ]);

        $this->service->trackProgress($asset->id, 100, 50);

        $this->assertDatabaseHas('reading_histories', [
            'user_id' => $this->user->id,
            'digital_asset_id' => $asset->id,
            'progress' => 100,
            'completed_at' => now(),
        ]);
    }

    public function test_search_returns_active_assets_matching_query(): void
    {
        Storage::fake('public');

        $file1 = UploadedFile::fake()->create('math.pdf', 100, 'application/pdf');
        $file2 = UploadedFile::fake()->create('science.pdf', 100, 'application/pdf');

        $this->service->upload($file1, ['title' => 'Advanced Mathematics', 'access_level' => 'public']);
        $this->service->upload($file2, ['title' => 'Computer Science', 'access_level' => 'public']);

        $results = $this->service->search('Mathematics');

        $this->assertCount(1, $results->items());
        $this->assertSame('Advanced Mathematics', $results->items()[0]->title);
    }

    public function test_search_respects_type_filter(): void
    {
        Storage::fake('public');

        $pdf = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $video = UploadedFile::fake()->create('vid.mp4', 100, 'video/mp4');

        $this->service->upload($pdf, ['title' => 'PDF Document', 'access_level' => 'public']);
        $this->service->upload($video, ['title' => 'Video File', 'access_level' => 'public']);

        $results = $this->service->search('', ['type' => 'video']);

        $this->assertCount(1, $results->items());
        $this->assertSame('Video File', $results->items()[0]->title);
    }

    public function test_search_filters_by_category(): void
    {
        Storage::fake('public');

        $category = DigitalAssetCategory::create(['name' => 'Academic', 'slug' => 'academic']);

        $file1 = UploadedFile::fake()->create('a.pdf', 100, 'application/pdf');
        $file2 = UploadedFile::fake()->create('b.pdf', 100, 'application/pdf');

        $this->service->upload($file1, ['title' => 'Category Doc', 'access_level' => 'public', 'category_id' => $category->id]);
        $this->service->upload($file2, ['title' => 'Other Doc', 'access_level' => 'public']);

        $results = $this->service->search('', ['category_id' => $category->id]);

        $this->assertCount(1, $results->items());
        $this->assertSame('Category Doc', $results->items()[0]->title);
    }
}
