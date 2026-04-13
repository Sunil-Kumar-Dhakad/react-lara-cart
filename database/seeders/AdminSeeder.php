<?php
// database/seeders/AdminSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use App\Models\Product;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin Users ────────────────────────────────────────────────────
        $users = [
            ['name'=>'Super Admin',   'email'=>'superadmin@nexus.io', 'role'=>'super_admin', 'password'=>'password'],
            ['name'=>'Admin User',    'email'=>'admin@nexus.io',      'role'=>'admin',        'password'=>'password'],
            ['name'=>'Accountant',    'email'=>'accounts@nexus.io',   'role'=>'accountant',   'password'=>'password'],
            ['name'=>'HR Manager',    'email'=>'hr@nexus.io',         'role'=>'hr',           'password'=>'password'],
            ['name'=>'Viewer',        'email'=>'viewer@nexus.io',     'role'=>'viewer',       'password'=>'password'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['email' => $u['email']], [
                'name'             => $u['name'],
                'password'         => Hash::make($u['password']),
                'role'             => $u['role'],
                'is_active'        => true,
                'email_verified_at'=> now(),
            ]);
        }

        // ── Sample Employees ───────────────────────────────────────────────
        $employees = [
            ['name'=>'Arjun Sharma',  'email'=>'arjun@nexus.io',  'role'=>'Developer',     'department'=>'Engineering', 'salary'=>85000,  'status'=>'active'],
            ['name'=>'Priya Patel',   'email'=>'priya@nexus.io',  'role'=>'Designer',      'department'=>'Design',      'salary'=>72000,  'status'=>'active'],
            ['name'=>'Rahul Gupta',   'email'=>'rahul@nexus.io',  'role'=>'Manager',       'department'=>'Sales',       'salary'=>95000,  'status'=>'active'],
            ['name'=>'Sneha Joshi',   'email'=>'sneha@nexus.io',  'role'=>'Analyst',       'department'=>'Finance',     'salary'=>68000,  'status'=>'inactive'],
            ['name'=>'Vikram Singh',  'email'=>'vikram@nexus.io', 'role'=>'DevOps',        'department'=>'Engineering', 'salary'=>90000,  'status'=>'active'],
            ['name'=>'Meera Nair',    'email'=>'meera@nexus.io',  'role'=>'HR Lead',       'department'=>'HR',          'salary'=>78000,  'status'=>'active'],
        ];

        foreach ($employees as $e) {
            Employee::updateOrCreate(['email' => $e['email']], array_merge($e, ['joined_at' => now()->subMonths(rand(1,24))]));
        }

        // ── Sample Products ────────────────────────────────────────────────
        $products = [
            ['name'=>'Enterprise Suite Pro',  'sku'=>'ESP-001','category'=>'Software',       'price'=>2499,'original_price'=>2999,'stock'=>150,'status'=>'active','badge'=>'bestseller'],
            ['name'=>'CloudSync Manager',     'sku'=>'CSM-002','category'=>'Cloud',          'price'=>1299,'original_price'=>1599,'stock'=>300,'status'=>'active','badge'=>'new'],
            ['name'=>'Analytics Dashboard',   'sku'=>'ADM-003','category'=>'Analytics',      'price'=>899, 'original_price'=>null, 'stock'=>500,'status'=>'active','badge'=>null],
            ['name'=>'Security Shield Elite', 'sku'=>'SSE-004','category'=>'Security',       'price'=>3999,'original_price'=>4999,'stock'=>80, 'status'=>'active','badge'=>'premium'],
            ['name'=>'API Gateway Pro',       'sku'=>'AGP-005','category'=>'Infrastructure', 'price'=>1799,'original_price'=>2199,'stock'=>0,  'status'=>'inactive','badge'=>null],
            ['name'=>'Data Vault Storage',    'sku'=>'DVS-006','category'=>'Infrastructure', 'price'=>599, 'original_price'=>null, 'stock'=>450,'status'=>'active','badge'=>'new'],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['sku' => $p['sku']], array_merge($p, [
                'description' => 'High-quality enterprise software solution for modern businesses.',
                'rating'      => round(rand(40, 50) / 10, 1),
            ]));
        }

        $this->command->info('✓ Admin users seeded. Login: superadmin@nexus.io / password');
    }
}
