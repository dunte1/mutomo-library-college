<?php

namespace Database\Seeders;

use App\Modules\Catalog\Models\Author;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Publisher;
use App\Modules\Catalog\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Medical Sciences', 'slug' => 'medical-sciences', 'children' => [
                ['name' => 'Anatomy', 'slug' => 'anatomy'],
                ['name' => 'Physiology', 'slug' => 'physiology'],
                ['name' => 'Pathology', 'slug' => 'pathology'],
                ['name' => 'Pharmacology', 'slug' => 'pharmacology'],
            ]],
            ['name' => 'Nursing', 'slug' => 'nursing', 'children' => [
                ['name' => 'Medical-Surgical Nursing', 'slug' => 'med-surg-nursing'],
                ['name' => 'Pediatric Nursing', 'slug' => 'pediatric-nursing'],
                ['name' => 'Mental Health Nursing', 'slug' => 'mental-health-nursing'],
                ['name' => 'Community Health Nursing', 'slug' => 'community-health-nursing'],
            ]],
            ['name' => 'Clinical Medicine', 'slug' => 'clinical-medicine', 'children' => [
                ['name' => 'Internal Medicine', 'slug' => 'internal-medicine'],
                ['name' => 'Pediatrics', 'slug' => 'pediatrics'],
                ['name' => 'Surgery', 'slug' => 'surgery'],
                ['name' => 'Obstetrics & Gynecology', 'slug' => 'obstetrics-gynecology'],
            ]],
            ['name' => 'Pharmacy', 'slug' => 'pharmacy', 'children' => [
                ['name' => 'Pharmaceutics', 'slug' => 'pharmaceutics'],
                ['name' => 'Clinical Pharmacy', 'slug' => 'clinical-pharmacy'],
            ]],
            ['name' => 'Public Health', 'slug' => 'public-health', 'children' => [
                ['name' => 'Epidemiology', 'slug' => 'epidemiology'],
                ['name' => 'Environmental Health', 'slug' => 'environmental-health'],
            ]],
            ['name' => 'Health Records', 'slug' => 'health-records'],
            ['name' => 'Medical Laboratory', 'slug' => 'medical-laboratory'],
            ['name' => 'General Reference', 'slug' => 'general-reference'],
        ];

        foreach ($categories as $catData) {
            $children = $catData['children'] ?? [];
            unset($catData['children']);
            $parent = Category::create($catData);
            foreach ($children as $child) {
                $child['parent_id'] = $parent->id;
                Category::create($child);
            }
        }

        $subjects = [
            ['name' => 'Human Anatomy', 'slug' => 'human-anatomy'],
            ['name' => 'Pathophysiology', 'slug' => 'pathophysiology'],
            ['name' => 'Pharmacotherapeutics', 'slug' => 'pharmacotherapeutics'],
            ['name' => 'Medical Ethics', 'slug' => 'medical-ethics'],
            ['name' => 'Research Methodology', 'slug' => 'research-methodology'],
            ['name' => 'Biostatistics', 'slug' => 'biostatistics'],
            ['name' => 'Clinical Skills', 'slug' => 'clinical-skills'],
            ['name' => 'Nutrition', 'slug' => 'nutrition'],
            ['name' => 'Health Informatics', 'slug' => 'health-informatics'],
            ['name' => 'Leadership in Healthcare', 'slug' => 'leadership-healthcare'],
        ];

        foreach ($subjects as $subj) {
            Subject::create($subj);
        }

        $publishers = [
            ['name' => 'Elsevier Health Sciences', 'slug' => 'elsevier-health'],
            ['name' => 'Lippincott Williams & Wilkins', 'slug' => 'lippincott'],
            ['name' => 'McGraw-Hill Medical', 'slug' => 'mcgraw-hill-medical'],
            ['name' => 'Oxford University Press', 'slug' => 'oxford-university-press'],
            ['name' => 'Springer Nature', 'slug' => 'springer-nature'],
            ['name' => 'Cambridge University Press', 'slug' => 'cambridge-up'],
            ['name' => 'Kenya Medical Association', 'slug' => 'kma'],
            ['name' => 'African Medical Research Foundation', 'slug' => 'amref'],
        ];

        foreach ($publishers as $pub) {
            Publisher::create($pub);
        }

        $authors = [
            ['name' => 'Gray\'s Anatomy for Students', 'slug' => 'grays-anatomy'],
            ['name' => 'Guyton and Hall', 'slug' => 'guyton-hall'],
            ['name' => 'Kumar and Clark', 'slug' => 'kumar-clark'],
            ['name' => 'Brunner and Suddarth', 'slug' => 'brunner-suddarth'],
            ['name' => 'Katzung', 'slug' => 'katzung'],
            ['name' => 'Robbins and Cotran', 'slug' => 'robbins-cotran'],
            ['name' => 'Nelson', 'slug' => 'nelson'],
            ['name' => 'Williams Obstetrics', 'slug' => 'williams-obstetrics'],
            ['name' => 'Bailey and Love', 'slug' => 'bailey-love'],
            ['name' => 'Park\'s Textbook', 'slug' => 'parks-textbook'],
        ];

        foreach ($authors as $auth) {
            Author::create($auth);
        }

        $books = [
            [
                'title' => 'Gray\'s Anatomy for Students',
                'isbn' => '9780323393041',
                'description' => 'The definitive textbook of human anatomy, presenting the core anatomical concepts in a clear and concise manner.',
                'pages' => 1192, 'publication_year' => 2023, 'edition' => '4th Edition',
                'publisher_id' => 1, 'category_id' => 2, 'authors' => [1], 'subjects' => [1],
                'copies_count' => 5, 'shelf_location' => 'A-01-01',
            ],
            [
                'title' => 'Guyton and Hall Textbook of Medical Physiology',
                'isbn' => '9780323597128',
                'description' => 'The leading textbook on medical physiology, covering all aspects of human physiology with clinical correlations.',
                'pages' => 1136, 'publication_year' => 2020, 'edition' => '14th Edition',
                'publisher_id' => 1, 'category_id' => 3, 'authors' => [2], 'subjects' => [2],
                'copies_count' => 4, 'shelf_location' => 'A-01-02',
            ],
            [
                'title' => 'Brunner & Suddarth\'s Textbook of Medical-Surgical Nursing',
                'isbn' => '9781975161037',
                'description' => 'Comprehensive nursing textbook covering medical-surgical nursing with evidence-based practice.',
                'pages' => 2272, 'publication_year' => 2022, 'edition' => '15th Edition',
                'publisher_id' => 2, 'category_id' => 6, 'authors' => [4], 'subjects' => [7],
                'copies_count' => 6, 'shelf_location' => 'B-01-01',
            ],
            [
                'title' => 'Basic and Clinical Pharmacology',
                'isbn' => '9781260452310',
                'description' => 'A comprehensive pharmacology textbook covering basic principles and clinical applications.',
                'pages' => 1312, 'publication_year' => 2021, 'edition' => '15th Edition',
                'publisher_id' => 3, 'category_id' => 4, 'authors' => [5], 'subjects' => [3],
                'copies_count' => 4, 'shelf_location' => 'C-01-01',
            ],
            [
                'title' => 'Robbins & Cotran Pathologic Basis of Disease',
                'isbn' => '9780323531139',
                'description' => 'The leading pathology textbook covering the fundamental mechanisms of disease.',
                'pages' => 1472, 'publication_year' => 2020, 'edition' => '10th Edition',
                'publisher_id' => 1, 'category_id' => 4, 'authors' => [6], 'subjects' => [2],
                'copies_count' => 3, 'shelf_location' => 'A-02-01',
            ],
            [
                'title' => 'Nelson Textbook of Pediatrics',
                'isbn' => '9780323529501',
                'description' => 'The definitive pediatrics reference covering all aspects of child health and disease.',
                'pages' => 4320, 'publication_year' => 2019, 'edition' => '21st Edition',
                'publisher_id' => 1, 'category_id' => 11, 'authors' => [7], 'subjects' => [7],
                'copies_count' => 3, 'shelf_location' => 'D-01-01',
            ],
            [
                'title' => 'Park\'s Textbook of Preventive and Social Medicine',
                'isbn' => '9789387963403',
                'description' => 'Comprehensive public health textbook focusing on preventive medicine and community health.',
                'pages' => 1068, 'publication_year' => 2022, 'edition' => '26th Edition',
                'publisher_id' => 7, 'category_id' => 17, 'authors' => [10], 'subjects' => [4],
                'copies_count' => 5, 'shelf_location' => 'E-01-01',
            ],
            [
                'title' => 'Bailey & Love\'s Short Practice of Surgery',
                'isbn' => '9780367548162',
                'description' => 'The classic surgical textbook covering all aspects of general surgery.',
                'pages' => 1544, 'publication_year' => 2022, 'edition' => '28th Edition',
                'publisher_id' => 5, 'category_id' => 12, 'authors' => [9], 'subjects' => [7],
                'copies_count' => 4, 'shelf_location' => 'D-02-01',
            ],
            [
                'title' => 'Williams Obstetrics',
                'isbn' => '9781260455267',
                'description' => 'The premier obstetrics textbook covering all aspects of pregnancy and childbirth.',
                'pages' => 1360, 'publication_year' => 2021, 'edition' => '26th Edition',
                'publisher_id' => 3, 'category_id' => 13, 'authors' => [8], 'subjects' => [7],
                'copies_count' => 3, 'shelf_location' => 'D-03-01',
            ],
            [
                'title' => 'Kumar and Clark\'s Clinical Medicine',
                'isbn' => '9780702078330',
                'description' => 'Essential textbook for clinical medicine covering all major medical specialties.',
                'pages' => 1472, 'publication_year' => 2021, 'edition' => '10th Edition',
                'publisher_id' => 1, 'category_id' => 10, 'authors' => [3], 'subjects' => [7],
                'copies_count' => 5, 'shelf_location' => 'D-04-01',
            ],
        ];

        foreach ($books as $bookData) {
            $authorsIds = $bookData['authors'];
            $subjectsIds = $bookData['subjects'];
            $copiesCount = $bookData['copies_count'];
            $shelfLocation = $bookData['shelf_location'];

            unset($bookData['authors'], $bookData['subjects'], $bookData['copies_count'], $bookData['shelf_location']);

            $bookData['slug'] = $bookData['slug'] ?? Str::slug($bookData['title']);
            $bookData['language'] = 'en';
            $bookData['price'] = fake()->randomFloat(2, 1500, 8500);
            $bookData['dewey_decimal'] = fake()->numerify('###.##');

            $book = Book::create($bookData);
            $book->authors()->sync($authorsIds);
            $book->subjects()->sync($subjectsIds);

            for ($i = 0; $i < $copiesCount; $i++) {
                BookCopy::create([
                    'book_id' => $book->id,
                    'barcode' => 'OLLMCHS-' . str_pad($book->id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'shelf_location' => $shelfLocation,
                    'status' => 'available',
                    'condition' => 'new',
                    'acquired_at' => now()->subMonths(rand(1, 12)),
                    'price' => $bookData['price'],
                ]);
            }
        }
    }
}
