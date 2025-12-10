<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AssetSeeder::class);

        $user1 = User::create([
            'name' => 'Bilal Haidar',
            'email' => 'aspmvp@gmail.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => now(),
            'balance' => '1000.00000000',
            'locked_balance' => '0.00000000',
        ]);

        $user2 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => now(),
            'balance' => '1000.00000000',
            'locked_balance' => '0.00000000',
        ]);

        $btc = Asset::where('symbol', 'BTC')->first();
        $eth = Asset::where('symbol', 'ETH')->first();

        $user1->assets()->attach($btc->id, [
            'balance' => '5.00000000',
            'locked_amount' => '0.00000000',
        ]);

        $user1->assets()->attach($eth->id, [
            'balance' => '10.00000000',
            'locked_amount' => '0.00000000',
        ]);

        $user2->assets()->attach($btc->id, [
            'balance' => '5.00000000',
            'locked_amount' => '0.00000000',
        ]);

        $user2->assets()->attach($eth->id, [
            'balance' => '10.00000000',
            'locked_amount' => '0.00000000',
        ]);
    }
}
