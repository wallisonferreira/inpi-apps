<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Revistas;
use App\Livewire\Processos;
use App\Livewire\Marcas;

Route::get('/dashboard', Revistas::class)->name('dashboard');
Route::get('/revistas', Revistas::class)->name('revistas');
Route::get('/processos', Processos::class)->name('processos');
Route::get('/marcas', Marcas::class)->name('marcas');
