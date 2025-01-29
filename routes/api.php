<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::post('/upload/settings',[ApiController::class, 'apiCreateSetting']);
Route::post('/upload/single',[ApiController::class, 'apiFileUploadSingle']);
Route::post('/upload/multiple',[ApiController::class, 'apiFileUploadMultiple']);
Route::post('/upload/attachments',[ApiController::class, 'apiShowFiles']);

Route::post('/upload/attachments/preview/{given_uid}',[ApiController::class, 'apiPreviewFiles']);
Route::put('/upload/titles',[ApiController::class, 'apiUpdateTitles']);

Route::post('/download',[ApiController::class, 'apiDownloadFile']);

Route::get('/upload/checkpassrequirement/{given_uid}',[ApiController::class,'apiIsPassRequiredSetting']);
Route::get('/upload/checkpassrequirementone/{given_uid}',[ApiController::class,'apiIsPassRequiredSingleFile']);
Route::post('/upload/attachsingle/{given_uid}',[ApiController::class, 'apiShowOneFile']);
Route::post('/upload/attachments/{given_uid}',[ApiController::class, 'apiShowMultipleFiles']);
Route::delete('/upload/attachsingle/delete/{given_uid}',[ApiController::class, 'apiDeleteOneFile']);
Route::delete('/upload/attachments/delete/{given_uid}',[ApiController::class, 'apiDeleteMultipleFiles']);

Route::get('/upload/mirrorsexpiry',[ApiController::class, 'apiGetMirrorsExpiry']);

Route::post('/upload/textupload',[ApiController::class, 'apiTextUpload']);
Route::post('/upload/showtexts',[ApiController::class, 'apiShowTexts']);
Route::post('/upload/showtexts/{given_uid}',[ApiController::class, 'apiShowTexts']);
Route::delete('/upload/showtexts/delete/{given_uid}',[ApiController::class, 'apiDeleteTexts']);