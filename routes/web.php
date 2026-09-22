<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'DiagramEditor')->name('dashboard');
    Route::inertia('architect', 'DiagramEditor')->name('architect');
    Route::inertia('workspaces', 'WorkspaceManagement')->name('workspaces');
});

require __DIR__.'/settings.php';
