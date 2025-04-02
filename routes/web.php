<?php

use App\Livewire\AboutPage;
use App\Livewire\IndexPage;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', IndexPage::class)->name('welcome');
Route::get('/about', AboutPage::class)->name('about');
