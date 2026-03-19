<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| A função test() é usada para definir testes com asserções
| A função test()->group() agrupa testes relacionados
|
| Aqui você também pode usar expect() com assertions do Pest
|
*/

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Feature Tests Setup
|--------------------------------------------------------------------------
|
| Todos os testes feature usam RefreshDatabase por padrão
| (configurado em phpunit.xml e pest.php)
|
*/

uses(TestCase::class, RefreshDatabase::class)
    ->beforeEach(fn () => $this->seed())
    ->in('Feature');
