<?php

namespace App\Modules\Members\Livewire;

use App\Models\Department;
use App\Models\Program;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\MemberService;
use Livewire\Component;
use Livewire\WithFileUploads;

class MemberBulkImport extends Component
{
    use WithFileUploads;

    public $csvFile = null;
    public bool $preview = false;
    public array $parsedRows = [];
    public array $importErrors = [];
    public int $successCount = 0;
    public int $errorCount = 0;
    public bool $importing = false;
    public bool $completed = false;

    protected function rules(): array
    {
        return [
            'csvFile' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }

    public function updatedCsvFile(): void
    {
        $this->validate();
        $this->preview = false;
        $this->parsedRows = [];
        $this->importErrors = [];
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->completed = false;
    }

    public function parse(): void
    {
        $this->validate();

        $this->importErrors = [];
        $this->parsedRows = [];
        $this->preview = false;

        $path = $this->csvFile->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            $this->dispatch('notify', type: 'error', message: 'Could not read the uploaded file.');
            return;
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            $this->dispatch('notify', type: 'error', message: 'CSV file appears to be empty or has no header row.');
            return;
        }

        // Normalize headers
        $headers = array_map(fn ($h) => strtolower(trim($h)), $headers);

        // Expected columns mapping
        $columnMap = [
            'first_name' => ['first name', 'firstname', 'first_name', 'given name', 'givenname'],
            'last_name' => ['last name', 'lastname', 'last_name', 'surname', 'family name', 'familyname'],
            'email' => ['email', 'e-mail', 'email address', 'emailaddress'],
            'phone' => ['phone', 'telephone', 'mobile', 'phone number', 'phonenumber', 'contact'],
            'admission_number' => ['admission number', 'admission_number', 'admission no', 'admissionno', 'adm no', 'admno', 'student id'],
            'id_number' => ['id number', 'id_number', 'national id', 'national_id', 'identity number'],
            'gender' => ['gender', 'sex'],
            'date_of_birth' => ['date of birth', 'date_of_birth', 'dob', 'birth date', 'birthdate'],
            'class' => ['class', 'grade', 'form', 'year', 'year_of_study', 'year of study'],
            'department' => ['department', 'dept', 'faculty'],
            'program' => ['program', 'programme', 'course'],
            'membership_type' => ['membership type', 'membership_type', 'member type', 'member_type', 'type'],
            'address' => ['address', 'physical address'],
        ];

        // Build a mapping from CSV columns to our field names
        $fieldMapping = [];
        foreach ($headers as $index => $header) {
            foreach ($columnMap as $field => $aliases) {
                if (in_array($header, $aliases)) {
                    $fieldMapping[$index] = $field;
                    break;
                }
            }
        }

        // Validate required columns
        $foundFirst = false;
        $foundLast = false;
        $foundEmail = false;
        foreach ($fieldMapping as $field) {
            if ($field === 'first_name') $foundFirst = true;
            if ($field === 'last_name') $foundLast = true;
            if ($field === 'email') $foundEmail = true;
        }

        if (!$foundFirst || !$foundLast || !$foundEmail) {
            fclose($handle);
            $this->importErrors[] = 'CSV must have at least these columns: first_name, last_name, email. Found headers: ' . implode(', ', $headers);
            return;
        }

        // Cache departments and programs for lookup
        $departments = Department::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id]);
        $programs = Program::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id]);

        $rowIndex = 0;
        $validRows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowIndex++;
            $rowNum = $rowIndex + 2; // row 1 is the header, so first data row is row 2
            $record = [];
            $rowErrors = [];

            // Map CSV columns to record fields
            foreach ($fieldMapping as $colIndex => $field) {
                $value = isset($row[$colIndex]) ? trim($row[$colIndex]) : '';
                $record[$field] = $value;
            }

            // Validate required fields
            if (empty($record['first_name'])) {
                $rowErrors[] = 'First name is required';
            }
            if (empty($record['last_name'])) {
                $rowErrors[] = 'Last name is required';
            }
            if (empty($record['email'])) {
                $rowErrors[] = 'Email is required';
            } elseif (!filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'Invalid email format';
            }

            // Check for duplicate email in the system
            if (!empty($record['email'])) {
                $existingMember = Member::where('email', $record['email'])->exists();
                if ($existingMember) {
                    $rowErrors[] = "Email '{$record['email']}' already exists";
                }
            }

            // Validate membership type
            $validTypes = [Member::MEMBERSHIP_STUDENT, Member::MEMBERSHIP_TEACHER, Member::MEMBERSHIP_STAFF, Member::MEMBERSHIP_EXTERNAL];
            if (!empty($record['membership_type']) && !in_array(strtolower($record['membership_type']), $validTypes)) {
                $rowErrors[] = "Invalid membership type '{$record['membership_type']}'. Must be: student, teacher, staff, or external";
            }

            // Look up department
            if (!empty($record['department'])) {
                $deptId = $departments[strtolower($record['department'])] ?? null;
                if ($deptId) {
                    $record['department_id'] = $deptId;
                } else {
                    $rowErrors[] = "Department '{$record['department']}' not found";
                }
            }
            unset($record['department']);

            // Look up program
            if (!empty($record['program'])) {
                $progId = $programs[strtolower($record['program'])] ?? null;
                if ($progId) {
                    $record['program_id'] = $progId;
                } else {
                    $rowErrors[] = "Program '{$record['program']}' not found";
                }
            }
            unset($record['program']);

            // Set defaults
            if (empty($record['membership_type'])) {
                $record['membership_type'] = Member::MEMBERSHIP_STUDENT;
            }
            $record['membership_type'] = strtolower($record['membership_type']);

            // Validate gender
            if (!empty($record['gender']) && !in_array(strtolower($record['gender']), ['male', 'female', 'other'])) {
                $rowErrors[] = "Invalid gender '{$record['gender']}'. Must be: male, female, or other";
            }
            if (!empty($record['gender'])) {
                $record['gender'] = strtolower($record['gender']);
            }

            if (!empty($rowErrors)) {
                $this->importErrors[] = [
                    'row' => $rowNum,
                    'name' => ($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''),
                    'email' => $record['email'] ?? '',
                    'errors' => implode('; ', $rowErrors),
                ];
                continue;
            }

            $validRows[] = $record;
        }

        fclose($handle);

        $this->parsedRows = $validRows;
        $this->preview = true;

        if (empty($this->importErrors) && empty($this->parsedRows)) {
            $this->dispatch('notify', type: 'warning', message: 'No valid rows found in the CSV file.');
        }
    }

    public function import(): void
    {
        if (empty($this->parsedRows)) {
            return;
        }

        $this->importing = true;
        $this->successCount = 0;
        $this->errorCount = count($this->importErrors);

        $service = app(MemberService::class);

        foreach ($this->parsedRows as $record) {
            try {
                $data = [
                    'first_name' => $record['first_name'],
                    'last_name' => $record['last_name'],
                    'email' => $record['email'],
                    'phone' => $record['phone'] ?? null,
                    'admission_number' => $record['admission_number'] ?? null,
                    'id_number' => $record['id_number'] ?? null,
                    'gender' => $record['gender'] ?? null,
                    'date_of_birth' => $record['date_of_birth'] ?? null,
                    'class' => $record['class'] ?? null, // mapped as Year of Study
                    'department_id' => $record['department_id'] ?? null,
                    'program_id' => $record['program_id'] ?? null,
                    'address' => $record['address'] ?? null,
                    'membership_type' => $record['membership_type'],
                    'status' => Member::STATUS_ACTIVE,
                    'joined_at' => now()->toDateString(),
                    'expires_at' => now()->addYear()->toDateString(),
                    'create_user' => true,
                ];

                $service->registerMember($data);
                $this->successCount++;
            } catch (\Throwable $e) {
                $this->errorCount++;
                $this->importErrors[] = [
                    'row' => 'N/A',
                    'name' => ($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''),
                    'email' => $record['email'] ?? '',
                    'errors' => $e->getMessage(),
                ];
            }
        }

        $this->importing = false;
        $this->completed = true;
        $this->parsedRows = [];

        $this->dispatch('notify', type: 'success', message: "Import completed. {$this->successCount} member(s) created, {$this->errorCount} error(s).");
    }

    public function render()
    {
        return view('members::livewire.member-bulk-import');
    }
}
