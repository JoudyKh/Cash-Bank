<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wallets = [
            [
                'name' => 'cash Bank360 رصيدي',
                'icon' => 'image',
                'number' => '123456789123',
                'key' => 'cashBank360',
            ],
            [
                'name' => 'انستاباي',
                'icon' => 'image',
                'number' => '987654321032',
                'key' => 'instapay',
            ],
            [
                'name' => ' فودافون كاش',
                'icon' => 'image',
                'number' => '789654213012',
                'key' => 'vodafoneCash',
            ],
            [
                'name' => 'اورانج',
                'icon' => 'image',
                'number' => '231654987031',
                'key' => 'orange',
            ],
            [
                'name' => 'اتصالات',
                'icon' => 'image',
                'number' => '452136789103',
                'key' => 'etisalatCash',
            ],
            [
                'name' => 'وي باي',
                'icon' => 'image',
                'number' => '62130457945',
                'key' => 'wepay',
            ],
        ];
        Wallet::insert($wallets);
    }
}
