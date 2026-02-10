<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Publisher;


class PublisherSeeder extends Seeder
{
    public function run(): void
    {
        Publisher::create([
            'name' => 'Penguin Books',
            'notes' => 'International publishing house'
        ]);

        Publisher::create([
            'name' => 'O’Reilly Media',
            'notes' => 'Technology and programming books'
        ]);
    }
}
