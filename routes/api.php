<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::post('/upload/settings',[ApiController::class, 'apicreatesettings'])->name('createsettings');
Route::post('/upload/single',[ApiController::class, 'apifileuploadsingle'])->name('fileuploadsingle');
Route::post('/upload/multiple',[ApiController::class, 'apifileupload'])->name('fileupload');
Route::post('/upload/attachments',[ApiController::class, 'apishowfiles'])->name('showfiles');
Route::post('/upload/attachments/{given_uid}',[ApiController::class, 'apishowfiles'])->name('showfilesuid');
Route::delete('/upload/attachments/delete/{given_uid}',[ApiController::class, 'apideletefiles'])->name('deletefilesuid');

Route::post('/upload/textupload',[ApiController::class, 'apitextupload'])->name('textupload');
Route::post('/upload/showtexts',[ApiController::class, 'apishowtexts'])->name('showtexts');
Route::post('/upload/showtexts/{given_uid}',[ApiController::class, 'apishowtexts'])->name('showtextsuid');
Route::delete('/upload/showtexts/delete/{given_uid}',[ApiController::class, 'apideletetexts'])->name('deletetextsuid');