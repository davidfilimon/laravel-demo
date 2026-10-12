<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaskController::class, 'index'])->name('dashboard');
Route::patch('tasks/{task}/completion', [TaskController::class, 'completion'])->name('tasks.completion');
Route::resource('tasks', TaskController::class);
