<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Models\DigitalAssetCategory;
use App\Modules\DigitalLibrary\Models\ReadingHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DigitalLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $librarian = User::where('email', 'librarian@ollmchs.ac.ke')->first();
        $student = User::where('email', 'student@ollmchs.ac.ke')->first();
        $lecturer = User::where('email', 'lecturer@ollmchs.ac.ke')->first();
        $admin = User::first();

        $categories = DigitalAssetCategory::insert([
            ['name' => 'Anatomy & Physiology', 'slug' => 'anatomy-physiology', 'description' => 'Anatomy and physiology reference materials', 'is_active' => true, 'created_by' => $admin?->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Clinical Medicine', 'slug' => 'clinical-medicine', 'description' => 'Clinical medicine textbooks and guides', 'is_active' => true, 'created_by' => $admin?->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nursing & Midwifery', 'slug' => 'nursing-midwifery', 'description' => 'Nursing and midwifery resources', 'is_active' => true, 'created_by' => $admin?->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lecture Notes', 'slug' => 'lecture-notes', 'description' => 'Lecture notes and presentations', 'is_active' => true, 'created_by' => $admin?->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medical Journals', 'slug' => 'medical-journals', 'description' => 'Peer-reviewed medical journals', 'is_active' => true, 'created_by' => $admin?->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Research Papers', 'slug' => 'research-papers', 'description' => 'Student and faculty research papers', 'is_active' => true, 'created_by' => $admin?->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Storage::disk('public')->makeDirectory('digital-library/pdf');
        Storage::disk('public')->makeDirectory('digital-library/lecture_note');
        Storage::disk('public')->makeDirectory('digital-library/presentation');

        // Create placeholder files
        $placeholder = 'OLLMCHS Digital Library - Placeholder content. This is a sample document for demonstration purposes.';
        $files = [
            'digital-library/pdf/medical-terminology-guide.pdf' => 'Medical Terminology Guide - OLLMCHS Reference Material',
            'digital-library/pdf/nursing-procedures-handbook.pdf' => 'Nursing Procedures Handbook - Standard Clinical Protocols',
            'digital-library/pdf/kemri-clinical-guidelines.pdf' => 'KEMRI Clinical Guidelines 2025 Edition',
            'digital-library/lecture_note/anatomy-lecture-notes.docx' => 'Week 1-8: Anatomy Lecture Notes by Dr. Kamau',
            'digital-library/lecture_note/pharmacology-notes.docx' => 'Pharmacology Notes - Drug Classifications & Mechanisms',
            'digital-library/presentation/community-health-overview.pptx' => 'Community Health Overview - Presentation Slides',
        ];

        foreach ($files as $path => $content) {
            Storage::disk('public')->put($path, $content);
        }

        $assets = [
            [
                'title' => 'Medical Terminology Guide',
                'slug' => 'medical-terminology-guide',
                'description' => 'Comprehensive medical terminology reference for nursing and clinical medicine students.',
                'file_path' => 'digital-library/pdf/medical-terminology-guide.pdf',
                'file_type' => 'pdf', 'mime_type' => 'application/pdf',
                'file_size' => 2048576, 'file_extension' => 'pdf',
                'category_id' => 3, 'author' => 'Dr. Jane Mwangi',
                'publisher' => 'OLLMCHS Press', 'publication_year' => 2025,
                'keywords' => '["medical terminology","nursing","clinical","reference"]',
                'access_level' => 'public', 'allow_download' => true,
                'uploaded_by' => $librarian?->id,
            ],
            [
                'title' => 'Nursing Procedures Handbook',
                'slug' => 'nursing-procedures-handbook',
                'description' => 'Standard clinical procedures and protocols for nursing students.',
                'file_path' => 'digital-library/pdf/nursing-procedures-handbook.pdf',
                'file_type' => 'pdf', 'mime_type' => 'application/pdf',
                'file_size' => 3145728, 'file_extension' => 'pdf',
                'category_id' => 3, 'author' => 'Sr. Grace Akinyi',
                'publisher' => 'OLLMCHS Press', 'publication_year' => 2024,
                'keywords' => '["nursing","procedures","clinical","protocols"]',
                'access_level' => 'restricted', 'allow_download' => false,
                'uploaded_by' => $librarian?->id,
            ],
            [
                'title' => 'KEMRI Clinical Guidelines 2025',
                'slug' => 'kemri-clinical-guidelines-2025',
                'description' => 'Kenya Medical Research Institute clinical practice guidelines.',
                'file_path' => 'digital-library/pdf/kemri-clinical-guidelines.pdf',
                'file_type' => 'pdf', 'mime_type' => 'application/pdf',
                'file_size' => 5242880, 'file_extension' => 'pdf',
                'category_id' => 2, 'author' => 'KEMRI',
                'publisher' => 'Kenya Medical Research Institute', 'publication_year' => 2025,
                'keywords' => '["KEMRI","clinical","guidelines","Kenya","medical"]',
                'access_level' => 'public', 'allow_download' => true,
                'uploaded_by' => $librarian?->id,
            ],
            [
                'title' => 'Anatomy Lecture Notes (Week 1-8)',
                'slug' => 'anatomy-lecture-notes',
                'description' => 'Complete lecture notes covering Weeks 1-8 of Human Anatomy.',
                'file_path' => 'digital-library/lecture_note/anatomy-lecture-notes.docx',
                'file_type' => 'lecture_note', 'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_size' => 1572864, 'file_extension' => 'docx',
                'category_id' => 4, 'author' => 'Dr. Kamau',
                'keywords' => '["anatomy","lecture notes","human body","Dr. Kamau"]',
                'access_level' => 'restricted', 'allow_download' => true,
                'uploaded_by' => $lecturer?->id,
            ],
            [
                'title' => 'Pharmacology Notes - Drug Classifications',
                'slug' => 'pharmacology-notes',
                'description' => 'Comprehensive pharmacology notes covering drug classifications and mechanisms of action.',
                'file_path' => 'digital-library/lecture_note/pharmacology-notes.docx',
                'file_type' => 'lecture_note', 'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_size' => 2097152, 'file_extension' => 'docx',
                'category_id' => 4, 'author' => 'Dr. Wanjiku',
                'keywords' => '["pharmacology","drugs","classifications","mechanisms","Dr. Wanjiku"]',
                'access_level' => 'restricted', 'allow_download' => true,
                'uploaded_by' => $lecturer?->id,
            ],
            [
                'title' => 'Community Health Overview',
                'slug' => 'community-health-overview',
                'description' => 'Presentation slides covering community health concepts and practices.',
                'file_path' => 'digital-library/presentation/community-health-overview.pptx',
                'file_type' => 'presentation', 'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'file_size' => 4194304, 'file_extension' => 'pptx',
                'category_id' => 4, 'author' => 'Prof. Ochieng',
                'keywords' => '["community health","public health","presentation","Prof. Ochieng"]',
                'access_level' => 'public', 'allow_download' => true,
                'uploaded_by' => $lecturer?->id,
            ],
        ];

        foreach ($assets as $data) {
            $data['keywords'] = json_decode($data['keywords']);
            DigitalAsset::create($data);
        }

        // Reading history
        if ($student && $lecturer) {
            $asset1 = DigitalAsset::where('slug', 'medical-terminology-guide')->first();
            $asset4 = DigitalAsset::where('slug', 'anatomy-lecture-notes')->first();

            if ($asset1) {
                ReadingHistory::create([
                    'user_id' => $student->id,
                    'digital_asset_id' => $asset1->id,
                    'trackable_type' => DigitalAsset::class,
                    'trackable_id' => $asset1->id,
                    'started_at' => now()->subDays(7),
                    'completed_at' => now()->subDays(5),
                    'progress' => 100,
                    'duration_minutes' => 120,
                ]);
            }

            if ($asset4) {
                ReadingHistory::create([
                    'user_id' => $student->id,
                    'digital_asset_id' => $asset4->id,
                    'trackable_type' => DigitalAsset::class,
                    'trackable_id' => $asset4->id,
                    'started_at' => now()->subDays(2),
                    'progress' => 45,
                    'last_page' => 67,
                    'duration_minutes' => 45,
                ]);
            }
        }
    }
}
