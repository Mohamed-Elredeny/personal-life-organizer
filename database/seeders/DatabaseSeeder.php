<?php

namespace Database\Seeders;

use App\Models\Anniversary;
use App\Models\Category;
use App\Models\Goal;
use App\Models\Professor;
use App\Models\Scholarship;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $mohamed = User::updateOrCreate(
            ['email' => 'a.a@najidalqimam.sa'],
            [
                'name' => 'Mohamed Elredeny',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'default_currency' => 'SAR',
                'email_verified_at' => now(),
            ]
        );

        $partner = User::updateOrCreate(
            ['email' => 'partner@local'],
            [
                'name' => 'Partner',
                'password' => Hash::make('password'),
                'role' => 'partner',
                'default_currency' => 'SAR',
                'email_verified_at' => now(),
            ]
        );

        $sectors = [
            ['name' => 'AIU (ABET)',         'color' => '#0ea5e9', 'icon' => 'heroicon-o-academic-cap'],
            ['name' => 'Syncora SaaS',       'color' => '#10b981', 'icon' => 'heroicon-o-rocket-launch'],
            ['name' => 'PhD Outreach',       'color' => '#6366f1', 'icon' => 'heroicon-o-beaker'],
            ['name' => 'Personal Projects',  'color' => '#f59e0b', 'icon' => 'heroicon-o-code-bracket'],
        ];

        foreach ($sectors as $s) {
            Sector::updateOrCreate(
                ['user_id' => $mohamed->id, 'name' => $s['name']],
                array_merge($s, ['user_id' => $mohamed->id])
            );
        }

        $expenseCats = [
            ['name' => 'Housing',      'color' => '#6366f1', 'icon' => 'heroicon-o-home'],
            ['name' => 'Food',         'color' => '#f59e0b', 'icon' => 'heroicon-o-cake'],
            ['name' => 'Transport',    'color' => '#0ea5e9', 'icon' => 'heroicon-o-truck'],
            ['name' => 'Education',    'color' => '#8b5cf6', 'icon' => 'heroicon-o-academic-cap'],
            ['name' => 'Health',       'color' => '#ef4444', 'icon' => 'heroicon-o-heart'],
            ['name' => 'Shopping',     'color' => '#ec4899', 'icon' => 'heroicon-o-shopping-cart'],
            ['name' => 'Subscriptions','color' => '#64748b', 'icon' => 'heroicon-o-rectangle-stack'],
        ];
        foreach ($expenseCats as $c) {
            Category::updateOrCreate(
                ['user_id' => $mohamed->id, 'name' => $c['name'], 'type' => 'expense'],
                array_merge($c, ['user_id' => $mohamed->id, 'type' => 'expense', 'is_shared' => true])
            );
        }

        $incomeCats = [
            ['name' => 'Salary',       'color' => '#10b981', 'icon' => 'heroicon-o-banknotes'],
            ['name' => 'Freelance',    'color' => '#14b8a6', 'icon' => 'heroicon-o-briefcase'],
            ['name' => 'Side Project', 'color' => '#22c55e', 'icon' => 'heroicon-o-rocket-launch'],
        ];
        foreach ($incomeCats as $c) {
            Category::updateOrCreate(
                ['user_id' => $mohamed->id, 'name' => $c['name'], 'type' => 'income'],
                array_merge($c, ['user_id' => $mohamed->id, 'type' => 'income'])
            );
        }

        Goal::updateOrCreate(
            ['user_id' => $mohamed->id, 'title' => 'Get accepted to a US PhD program'],
            [
                'description' => 'Complete outreach, polish SoP, secure references, submit applications.',
                'category' => 'work', 'horizon' => 'long_term', 'status' => 'in_progress',
                'target_date' => now()->addMonths(6),
                'progress' => 15,
            ]
        );

        Goal::updateOrCreate(
            ['user_id' => $mohamed->id, 'title' => 'Launch Syncora MVP to first 10 paying SMEs'],
            [
                'description' => 'ZATCA-compliant Saudi POS, onboarding flow, billing.',
                'category' => 'work', 'horizon' => 'quarterly', 'status' => 'in_progress',
                'target_date' => now()->addMonths(3),
                'progress' => 40,
            ]
        );

        // Sample scholarships (so PhD section isn't empty)
        Scholarship::firstOrCreate(
            ['user_id' => $mohamed->id, 'name' => 'Fulbright Foreign Student Program'],
            [
                'university' => 'Multiple US universities',
                'country' => 'USA',
                'level' => 'phd',
                'status' => 'interested',
                'deadline' => now()->addMonths(2)->day(15),
                'amount' => 50000, 'currency' => 'USD',
                'funding_type' => 'Full',
                'url' => 'https://foreign.fulbrightonline.org/',
            ]
        );

        Scholarship::firstOrCreate(
            ['user_id' => $mohamed->id, 'name' => 'MIT EECS PhD'],
            [
                'university' => 'MIT', 'country' => 'USA', 'level' => 'phd',
                'status' => 'shortlisted',
                'deadline' => now()->addMonths(3)->day(1),
                'amount' => 45000, 'currency' => 'USD',
                'funding_type' => 'RA/TA',
            ]
        );

        // No professor seeded by default — left for user to add their own
    }
}
