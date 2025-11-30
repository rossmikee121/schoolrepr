<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Principal user
        $principal = User::create([
            'name' => 'Dr. Principal',
            'email' => 'principal@schoolerp.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        $principal->assignRole('principal');

        // Create HOD Commerce
        $hodCommerce = User::create([
            'name' => 'Prof. Commerce HOD',
            'email' => 'hod.commerce@schoolerp.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        $hodCommerce->assignRole('hod_commerce');

        // Create HOD Science
        $hodScience = User::create([
            'name' => 'Prof. Science HOD',
            'email' => 'hod.science@schoolerp.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        $hodScience->assignRole('hod_science');

        // Create a Class Teacher
        $classTeacher = User::create([
            'name' => 'Prof. Class Teacher',
            'email' => 'class.teacher@schoolerp.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        $classTeacher->assignRole('class_teacher');

        // Create a Subject Teacher
        $subjectTeacher = User::create([
            'name' => 'Prof. Subject Teacher',
            'email' => 'subject.teacher@schoolerp.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        $subjectTeacher->assignRole('subject_teacher');

        // Create Accounts Staff
        $accountsStaff = User::create([
            'name' => 'Accounts Officer',
            'email' => 'accounts@schoolerp.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        $accountsStaff->assignRole('accounts_staff');
    }
}