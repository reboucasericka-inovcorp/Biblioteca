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
| (configurado em phpunit.xml)
|
*/

uses(TestCase::class, RefreshDatabase::class)
    ->beforeEach(function (): void {
        $this->seed();
    })
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Unit Tests Setup
|--------------------------------------------------------------------------
|
| Testes unitários não precisam de RefreshDatabase
|
*/

uses(TestCase::class)
    ->in('Unit');
