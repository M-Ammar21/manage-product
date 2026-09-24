<?php

namespace Database\Seeders;

use App\Models\ApiToken;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['username' => 'jhon_doe'],
            [
                'name' => 'Jhon Doe',
                'email' => 'jhon.doe@example.test',
                'password' => Hash::make('supersecret'),
            ],
        );

        ApiToken::updateOrCreate(
            ['token_hash' => hash('sha256', 'dev-auth-token')],
            [
                'user_id' => $user->id,
                'refresh_token_hash' => hash('sha256', 'dev-refresh-token'),
                'expires_at' => now()->addYear(),
                'refresh_expires_at' => now()->addYear(),
            ],
        );

        collect([
            [
                'title' => 'Awesome T-Shirt',
                'price' => 99.99,
                'description' => 'High-quality cotton t-shirt',
                'category' => 'Clothes',
                'images' => ['https://placehold.co/640x480'],
            ],
            [
                'title' => 'Wireless Mouse',
                'price' => 149.50,
                'description' => 'Ergonomic wireless mouse for daily work',
                'category' => 'Electronics',
                'images' => ['https://placehold.co/640x480'],
            ],
            [
                'title' => 'Clean Code Book',
                'price' => 59.75,
                'description' => 'A practical book about writing maintainable code',
                'category' => 'Books',
                'images' => ['https://placehold.co/640x480'],
            ],
        ])->each(function (array $product) use ($user): void {
            Product::updateOrCreate(
                ['title' => $product['title']],
                [
                    ...$product,
                    'created_by_id' => $user->id,
                    'updated_by_id' => $user->id,
                ],
            );
        });
    }
}
