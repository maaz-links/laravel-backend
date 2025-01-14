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

Route::resource('securemirrors', App\Http\Controllers\MirrorController::class);
// GET /securemirrors – To view all secure mirrors.
// GET /securemirrors/create – To create a new secure mirror.
// POST /securemirrors – To store a new secure mirror.
// GET /securemirrors/{id} – To view a specific secure mirror.
// GET /securemirrors/{id}/edit – To edit an existing secure mirror.
// PUT /securemirrors/{id} – To update a secure mirror.
// DELETE /securemirrors/{id} – To delete a secure mirror.
