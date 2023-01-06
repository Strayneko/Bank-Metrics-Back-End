<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bank::insert([
            [
                'name' => 'Bank Of America',
                'marital_status' => 2,
                'max_age' => 60,
                'employment' => 1,
                'loaning_percentage' => 90,
                'nationality' => 2,
                'min_age' => 17,
            ],
            [
                'name' => 'Bank Standard',
                'marital_status' => 1,
                'max_age' => 50,
                'employment' => 2,
                'loaning_percentage' => 85,
                'nationality' => 2,
                'min_age' => 17,
            ],
            [
                'name' => 'World Bank',
                'marital_status' => 0,
                'max_age' => 70,
                'employment' => 2,
                'loaning_percentage' => 80,
                'nationality' => 0,
                'min_age' => 17,
            ],
        ]);
    }
}
