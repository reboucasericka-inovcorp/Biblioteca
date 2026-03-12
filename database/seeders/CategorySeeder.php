<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Programação',
            'Inteligência Artificial',
            'Desenvolvimento web',
            'Banco de dados',
            'Redes e Segurança',
            'DevOps',
            'Ciência de Dados',
            'Cloud Computing',
            'Livros em destaque',
            'Linguagens de Programação',
            'Frameworks',
            'Sistemas Operacionais',
            'Arquitetura de Software',
            'Machine Learning',
            'Big Data',
            'Blockchain',
            'Internet das Coisas',
            'Carreira em TI',
            'Certificações',
            'Ferramentas de Desenvolvimento',
            'Metodologias Ágeis',
            'Teste de Software',
            'UX/UI Design',
            'Infraestrutura',
            'Segurança da Informação',
            'Novidades em Tecnologia',
        ];

        foreach ($names as $i => $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $i + 1]
            );
        }
    }
}
