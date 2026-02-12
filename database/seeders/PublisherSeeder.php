<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Publisher;

class PublisherSeeder extends Seeder
{
    public function run(): void
    {
        $publishers = [
            ['name' => 'Penguin Books', 'notes' => 'International publishing house'],
            ['name' => 'O\'Reilly Media', 'notes' => 'Technology and programming books'],
            ['name' => 'HarperCollins', 'notes' => 'Major publisher of fiction and non-fiction'],
            ['name' => 'Editora Sextante', 'notes' => 'Brazilian publisher, bestsellers'],
            ['name' => 'Addison-Wesley', 'notes' => 'Technical and programming books'],
            ['name' => 'Prentice Hall', 'notes' => 'Educational and academic books'],
            ['name' => 'Editora Companhia das Letras', 'notes' => 'Brazilian literary publisher'],
        ];

        foreach ($publishers as $p) {
            Publisher::firstOrCreate(
                ['name' => $p['name']],
                ['notes' => $p['notes']]
            );
        }
    }
}
