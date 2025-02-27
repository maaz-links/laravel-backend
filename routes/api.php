<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\MiscController;
use App\Http\Controllers\SecuretextController;
use Illuminate\Support\Facades\Route;

Route::post('/send-email', [EmailController::class, 'sendEmail']);

Route::post('/upload/settings',[ApiController::class, 'apiCreateSetting']);
Route::post('/upload/single',[ApiController::class, 'apiFileUploadSingle']);
Route::post('/upload/multiple',[ApiController::class, 'apiFileUploadMultiple']);
Route::post('/upload/attachments',[ApiController::class, 'apiShowFiles']);

Route::post('/upload/preview/{given_uid}',[ApiController::class, 'apiPreview']);
Route::put('/upload/titles',[ApiController::class, 'apiUpdateTitles']);
Route::post('/upload/editsingle/{given_uid}',[ApiController::class, 'apiUpdateOneFile']);

Route::post('/download',[ApiController::class, 'apiDownloadFile']);

Route::get('/upload/checkpassrequirement/{given_uid}',[ApiController::class,'apiIsPassRequiredSetting']);
Route::get('/upload/checkpassrequirementone/{given_uid}',[ApiController::class,'apiIsPassRequiredSingleFile']);
Route::post('/upload/verifypass/{given_uid}',[ApiController::class,'apiVerifyPassword']);
Route::post('/upload/attachsingle/{given_uid}',[ApiController::class, 'apiShowOneFile']);
Route::post('/upload/attachments/{given_uid}',[ApiController::class, 'apiShowMultipleFiles']);
Route::delete('/upload/attachsingle/delete/{given_uid}',[ApiController::class, 'apiDeleteOneFile']);
Route::delete('/upload/attachments/delete/{given_uid}',[ApiController::class, 'apiDeleteMultipleFiles']);

Route::get('/miscdata',[MiscController::class, 'apiGetMiscData']);

Route::post('/upload/textupload',[SecuretextController::class, 'apiTextUpload']);
Route::post('/upload/showtexts',[SecuretextController::class, 'apiShowTexts']);
Route::post('/upload/showtexts/{given_uid}',[SecuretextController::class, 'apiShowTexts']);
Route::delete('/upload/showtexts/delete/{given_uid}',[SecuretextController::class, 'apiDeleteTexts']);

Route::delete('/upload/cleardata',[MiscController::class, 'apiCleardata']);
Route::delete('/upload/cleardata/{limit}',[MiscController::class, 'apiCleardata']);

Route::get('/files/{filename}', [MiscController::class, 'BringFile']);
Route::get('/thumbnails/{filename}', [MiscController::class, 'BringThumbnail']);