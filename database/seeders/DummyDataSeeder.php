<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Nursing', 'code' => 'NUR', 'description' => 'Department of Nursing Sciences'],
            ['name' => 'Clinical Medicine', 'code' => 'CM', 'description' => 'Department of Clinical Medicine'],
            ['name' => 'Pharmacy', 'code' => 'PHARM', 'description' => 'Department of Pharmacy'],
            ['name' => 'Medical Laboratory', 'code' => 'MLS', 'description' => 'Department of Medical Laboratory Sciences'],
            ['name' => 'Public Health', 'code' => 'PH', 'description' => 'Department of Public Health'],
            ['name' => 'Health Records', 'code' => 'HR', 'description' => 'Department of Health Records and IT'],
            ['name' => 'Library Services', 'code' => 'LIB', 'description' => 'Library Services Department'],
            ['name' => 'Administration', 'code' => 'ADMIN', 'description' => 'College Administration'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        $programs = [
            ['name' => 'Diploma in Nursing', 'code' => 'DIP-NUR', 'department_id' => 1, 'duration_years' => 3],
            ['name' => 'Certificate in Nursing', 'code' => 'CERT-NUR', 'department_id' => 1, 'duration_years' => 2],
            ['name' => 'Diploma in Clinical Medicine', 'code' => 'DIP-CM', 'department_id' => 2, 'duration_years' => 3],
            ['name' => 'Diploma in Pharmacy', 'code' => 'DIP-PHARM', 'department_id' => 3, 'duration_years' => 3],
            ['name' => 'Diploma in Medical Laboratory', 'code' => 'DIP-MLS', 'department_id' => 4, 'duration_years' => 3],
            ['name' => 'Diploma in Public Health', 'code' => 'DIP-PH', 'department_id' => 5, 'duration_years' => 3],
        ];

        foreach ($programs as $prog) {
            Program::firstOrCreate(['code' => $prog['code']], $prog);
        }

        $users = [
            [
                'name' => 'Super Administrator', 'email' => 'admin@ollmchs.ac.ke',
                'department_id' => 8, 'role' => 'super-admin',
            ],
            [
                'name' => 'Jane Librarian', 'email' => 'librarian@ollmchs.ac.ke',
                'department_id' => 7, 'employee_id' => 'LIB001', 'role' => 'librarian',
            ],
            [
                'name' => 'Peter Assistant', 'email' => 'assistant@ollmchs.ac.ke',
                'department_id' => 7, 'employee_id' => 'LIB002', 'role' => 'assistant-librarian',
            ],
            [
                'name' => 'John Student', 'email' => 'student@ollmchs.ac.ke',
                'department_id' => 1, 'program_id' => 1, 'admission_number' => 'OLLMCHS/2024/001',
                'academic_year' => '2024/2025', 'semester' => 1, 'role' => 'student',
            ],
            [
                'name' => 'Dr. Mary Lecturer', 'email' => 'lecturer@ollmchs.ac.ke',
                'department_id' => 1, 'employee_id' => 'LEC001', 'role' => 'lecturer',
            ],
            [
                'name' => 'Susan Finance', 'email' => 'finance@ollmchs.ac.ke',
                'department_id' => 8, 'employee_id' => 'FIN001', 'role' => 'finance-officer',
            ],
            [
                'name' => 'Dr. James HOD', 'email' => 'hod@ollmchs.ac.ke',
                'department_id' => 1, 'employee_id' => 'HOD001', 'role' => 'department-head',
            ],
            [
                'name' => 'Tom ICT', 'email' => 'ict@ollmchs.ac.ke',
                'department_id' => 6, 'employee_id' => 'ICT001', 'role' => 'ict-admin',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => bcrypt('password'),
                    'phone' => '+'.fake()->numerify('2547########'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ])
            );
            $user->assignRole($role);
        }
    }
}
