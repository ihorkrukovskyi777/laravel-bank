<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactionTypes = [
            [
                'name' => 'transfer',
                'display_name' => 'Переказ між рахунками',
                'description' => 'Переказ коштів між рахунками одного або різних користувачів',
                'fee_percentage' => 0.0000,
                'fee_fixed' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'payment',
                'display_name' => 'Платіж',
                'description' => 'Платіж за реквізитами або послуги',
                'fee_percentage' => 0.0100, // 1%
                'fee_fixed' => 5.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'deposit',
                'display_name' => 'Поповнення',
                'description' => 'Поповнення рахунку',
                'fee_percentage' => 0.0000,
                'fee_fixed' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'withdrawal',
                'display_name' => 'Зняття',
                'description' => 'Зняття коштів з рахунку',
                'fee_percentage' => 0.0050, // 0.5%
                'fee_fixed' => 10.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('transaction_types')->insert($transactionTypes);
    }
}
