<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Models\Author;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Publisher;
use App\Modules\Catalog\Services\BarcodeService;
use App\Modules\Shared\Helpers\AuditHelper;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Shuchkin\SimpleXLSX;

class BookBulkUpload extends Component
{
    use WithFileUploads;

    public $file = null;
    public array $preview = [];
    public array $uploadErrors = [];
    public int $step = 1;
    public bool $importing = false;
    public int $imported = 0;
    public int $failed = 0;

    protected function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ];
    }

    public function parse(): void
    {
        $this->validate();

        $this->uploadErrors = [];
        $this->preview = [];

        $path = $this->file->getRealPath();
        $extension = strtolower($this->file->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'])) {
            $this->parseExcel($path);
        } else {
            $this->parseCsv($path);
        }

        if (empty($this->preview)) {
            $this->addError('file', 'No valid book rows found in the file.');
            return;
        }

        $this->step = 2;
    }

    protected function parseCsv(string $path): void
    {
        $handle = fopen($path, 'r');

        $header = fgetcsv($handle);

        if (!$header) {
            $this->addError('file', 'Could not read CSV header row.');
            fclose($handle);
            return;
        }

        $header = array_map('trim', $header);
        $lowerHeader = array_map('strtolower', $header);
        $required = ['title'];
        $missing = array_diff($required, $lowerHeader);
        if (!empty($missing)) {
            $this->addError('file', 'Missing required columns: ' . implode(', ', $missing) . '. Required: title.');
            fclose($handle);
            return;
        }

        $keyMap = array_combine($header, $lowerHeader);

        $rowNumber = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $raw = array_combine($header, array_map('trim', $row));
            $data = [];
            foreach ($raw as $origKey => $value) {
                $data[$keyMap[$origKey]] = $value;
            }
            $this->processRow($data, $rowNumber);
        }

        fclose($handle);
    }

    protected function parseExcel(string $path): void
    {
        $xlsx = SimpleXLSX::parse($path);

        if (!$xlsx) {
            $this->addError('file', SimpleXLSX::parseError());
            return;
        }

        $rows = $xlsx->rows();
        if (empty($rows)) {
            $this->addError('file', 'Excel file is empty.');
            return;
        }

        $header = array_map('trim', $rows[0]);
        $lowerHeader = array_map('strtolower', $header);
        $required = ['title'];
        $missing = array_diff($required, $lowerHeader);
        if (!empty($missing)) {
            $this->addError('file', 'Missing required columns: ' . implode(', ', $missing) . '. Required: title.');
            return;
        }

        $keyMap = array_combine($header, $lowerHeader);

        $rowNumber = 0;
        for ($i = 1; $i < count($rows); $i++) {
            $rowNumber++;
            $data = [];
            foreach ($header as $colIndex => $colName) {
                $data[$keyMap[$colName]] = $rows[$i][$colIndex] ?? '';
            }
            $this->processRow($data, $rowNumber);
        }
    }

    protected function processRow(array $data, int $rowNumber): void
    {
        if (empty($data['title'])) {
            return;
        }

        $rowErrors = [];

        $this->preview[] = [
            'row' => $rowNumber,
            'title' => $data['title'] ?? '',
            'isbn' => $data['isbn'] ?? '',
            'authors' => $data['authors'] ?? '',
            'category' => $data['category'] ?? '',
            'publisher' => $data['publisher'] ?? '',
            'language' => $data['language'] ?? 'en',
            'pages' => $data['pages'] ?? '',
            'publication_year' => $data['publication_year'] ?? '',
            'edition' => $data['edition'] ?? '',
            'copies_count' => $data['copies_count'] ?? '1',
            'shelf_location' => $data['shelf_location'] ?? '',
            'price' => $data['price'] ?? '',
            '_errors' => $rowErrors,
        ];
    }

    public function import(): void
    {
        $this->importing = true;
        $this->imported = 0;
        $this->failed = 0;

        $barcodeService = app(BarcodeService::class);

        foreach ($this->preview as &$row) {
            if (!empty($row['_errors'])) {
                $this->failed++;
                continue;
            }

            try {
                $bookData = [
                    'title' => $row['title'],
                    'slug' => Str::slug($row['title']),
                    'isbn' => $row['isbn'] ?: null,
                    'language' => $row['language'] ?: 'en',
                    'pages' => $row['pages'] ? (int) $row['pages'] : null,
                    'publication_year' => $row['publication_year'] ? (int) $row['publication_year'] : null,
                    'edition' => $row['edition'] ?: null,
                ];

                if (!empty($row['category'])) {
                    $category = Category::where('name', $row['category'])->orWhere('slug', Str::slug($row['category']))->first();
                    if ($category) {
                        $bookData['category_id'] = $category->id;
                    }
                }

                if (!empty($row['publisher'])) {
                    $publisher = Publisher::where('name', $row['publisher'])->orWhere('slug', Str::slug($row['publisher']))->first();
                    if ($publisher) {
                        $bookData['publisher_id'] = $publisher->id;
                    }
                }

                if (!empty($row['price'])) {
                    $bookData['price'] = (float) $row['price'];
                }

                $book = Book::create($bookData);

                if (!empty($row['authors'])) {
                    $authorIds = [];
                    foreach (explode(',', $row['authors']) as $authorName) {
                        $authorName = trim($authorName);
                        if (empty($authorName)) continue;
                        $author = Author::firstOrCreate(
                            ['name' => $authorName],
                            ['slug' => Str::slug($authorName)]
                        );
                        $authorIds[] = $author->id;
                    }
                    if (!empty($authorIds)) {
                        $book->authors()->sync($authorIds);
                    }
                }

                $copiesCount = (int) ($row['copies_count'] ?? 1);
                for ($i = 0; $i < $copiesCount; $i++) {
                    $book->copies()->create([
                        'barcode' => $barcodeService->generate(),
                        'shelf_location' => $row['shelf_location'] ?: null,
                        'status' => 'available',
                        'condition' => 'new',
                        'acquired_at' => now(),
                        'price' => $bookData['price'] ?? null,
                    ]);
                }

                AuditHelper::log('bulk_imported', 'catalog', [
                    'book_id' => $book->id,
                    'title' => $book->title,
                ]);

                $this->imported++;
            } catch (\Throwable $e) {
                $this->failed++;
                $row['_errors'][] = $e->getMessage();
                \Illuminate\Support\Facades\Log::warning('Bulk import row failed', ['error' => $e->getMessage(), 'row' => $row['_title'] ?? 'unknown']);
            }
        }

        unset($row);
        $this->step = 3;
        $this->importing = false;
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Books Template');

        $headers = [
            'title', 'isbn', 'authors', 'category', 'publisher',
            'language', 'pages', 'publication_year', 'edition',
            'copies_count', 'shelf_location', 'price',
        ];

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:' . $lastColumn . '1')
            ->getFont()->setBold(true);

        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VISIBLE);


        $exampleRow = [
            'The Great Gatsby', '9780743273565', '9780743273565', 'F. Scott Fitzgerald',
            'Fiction', 'Scribner', 'en', '180', '1925', '1st', '3', 'A1-Shelf', '12.99',
        ];
        $sheet->fromArray([$exampleRow], null, 'A2');

        $sheet->getStyle('A1:' . $lastColumn . '1')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F0FE');

        $sheet->getStyle('A2:' . $lastColumn . '2')->getFont()->setColor(
            new Color('FF999999')
        );

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'template_') . '.xlsx';
        $writer->save($tempFile);

        return response()->download($tempFile, 'book_import_template.xlsx')
            ->deleteFileAfterSend(true);
    }

    public function resetUpload(): void
    {
        $this->reset(['file', 'preview', 'uploadErrors', 'step', 'imported', 'failed', 'importing']);
    }

    public function render()
    {
        return view('catalog::livewire.book-bulk-upload');
    }
}
