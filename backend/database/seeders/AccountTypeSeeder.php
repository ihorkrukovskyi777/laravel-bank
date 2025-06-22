<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountTypes = [
            [
                'name' => 'current',
                'display_name' => 'Поточний рахунок',
                'description' => 'Звичайний поточний рахунок для щоденних операцій',
                'min_balance' => 0.00,
                'monthly_fee' => 0.00,
                'interest_rate' => 0.0000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'savings',
                'display_name' => 'Ощадний рахунок',
                'description' => 'Рахунок для збереження коштів з нарахуванням відсотків',
                'min_balance' => 100.00,
                'monthly_fee' => 0.00,
                'interest_rate' => 0.0500, // 5% річних
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'credit',
                'display_name' => 'Кредитний рахунок',
                'description' => 'Рахунок для кредитних операцій',
                'min_balance' => 0.00,
                'monthly_fee' => 50.00,
                'interest_rate' => 0.1500, // 15% річних
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('account_types')->insert($accountTypes);
    }
}
