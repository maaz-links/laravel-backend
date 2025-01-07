<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/', function () {
    return view('welcome');
});

//FOR API ROUTES TESTING
Route::get('/fileform', [ApiController::class, 'fileform'])->name('fileform');

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/home/{blockid}', [App\Http\Controllers\HomeController::class, 'blockChange'])->name('blocker');
Route::get('/download/{fileurl}', [App\Http\Controllers\HomeController::class, 'GetURL'])->name('fileurl');
Route::get('/posts', [App\Http\Controllers\HomeController::class, 'posts'])->name('posts');
