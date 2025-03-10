<?php

use App\Http\Controllers\ConfigController;
use App\Http\Controllers\MailSettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpirationDurationController;
Route::get('/', function () {
    return redirect('/login');//view('welcome');
});

//FOR API ROUTES TESTING
Route::get('/fileform', [App\Http\Controllers\ApiController::class, 'fileform'])->name('fileform');

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/home/{blockid}', [App\Http\Controllers\HomeController::class, 'blockChange'])->name('blocker');
Route::get('/download/{fileurl}', [App\Http\Controllers\HomeController::class, 'GetURL'])->name('downloadfile');
Route::get('/thumbnail/{fileurl}', [App\Http\Controllers\HomeController::class, 'GetThumbnail'])->name('thumbnail');
Route::get('/posts', [App\Http\Controllers\HomeController::class, 'posts'])->name('posts');

Route::get('expirationdurations', [ExpirationDurationController::class, 'index'])->name('expirationdurations.index');
Route::get('expirationdurations/create', [ExpirationDurationController::class, 'create'])->name('expirationdurations.create');
Route::post('expirationdurations', [ExpirationDurationController::class, 'store'])->name('expirationdurations.store');
Route::get('expirationdurations/{id}/edit', [ExpirationDurationController::class, 'edit'])->name('expirationdurations.edit');
Route::put('expirationdurations/{id}', [ExpirationDurationController::class, 'update'])->name('expirationdurations.update');
Route::delete('expirationdurations/{id}', [ExpirationDurationController::class, 'destroy'])->name('expirationdurations.destroy');

Route::post('expirationdurations/unlimited', [ExpirationDurationController::class, 'unlimitedChange'])->name('expirationdurations.unlimited');

Route::resource('securemirrors', App\Http\Controllers\MirrorController::class);
// GET /securemirrors – To view all secure mirrors.
// GET /securemirrors/create – To create a new secure mirror.
// POST /securemirrors – To store a new secure mirror.
// GET /securemirrors/{id} – To view a specific secure mirror.
// GET /securemirrors/{id}/edit – To edit an existing secure mirror.
// PUT /securemirrors/{id} – To update a secure mirror.
// DELETE /securemirrors/{id} – To delete a secure mirror.


Route::get('/configs', [ConfigController::class, 'index'])->name('configs.index');
Route::get('/configs/create', [ConfigController::class, 'create'])->name('configs.create');
Route::post('/configs', [ConfigController::class, 'store'])->name('configs.store');
Route::get('/configs/{mail_config}/edit', [ConfigController::class, 'edit'])->name('configs.edit');
Route::put('/configs/{mail_config}', [ConfigController::class, 'update'])->name('configs.update');
Route::delete('/configs/{mail_config}', [ConfigController::class, 'destroy'])->name('configs.destroy');
