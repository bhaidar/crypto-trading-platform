<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['name' => 'Bitcoin', 'symbol' => 'BTC'],
            ['name' => 'Ethereum', 'symbol' => 'ETH'],
            ['name' => 'Litecoin', 'symbol' => 'LTC'],
        ];

        foreach ($assets as $asset) {
            Asset::firstOrCreate(
                ['symbol' => $asset['symbol']],
                ['name' => $asset['name']]
            );
        }
    }
}
