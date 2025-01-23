<?php

namespace App\Http\Controllers;

use App\Models\FilesSettings;
use App\Models\Securefile;
use App\Models\Securetext;
use App\Models\Securemirror;
use App\Models\Expirationduration;
use DB;
use File;
use Illuminate\Http\Request;
use Storage;
use Validator;
use FFMpeg\FFMpeg;
class ApiController extends Controller
{
    // Text Upload
    public function apiTextUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'textupload' => 'required|string',
            'expiry_date' => 'required|regex:/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{3})?Z)$/|after:today',
            'burn_after_read' => 'required|boolean',
            'password' => 'nullable|string',
            //'uid' => 'unique:files_settings',
            'ip' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $final_uid = $this->ApiControllerCheckUid($request->uid);
        $final_ip = $this->ApiControllerCheckIp($request);

        $storedsettings = $this->storeFileSettings($request, $final_uid, $final_ip, 1);

        Securetext::create([
            'content' => $request->textupload,
            'setting_id' => $storedsettings->id,
        ]);

        return response()->json(['message' => 'Text uploaded successfully', 'uid' => $final_uid], 201);
    }

    // Show Texts
    public function apiShowTexts(Request $request, $given_uid)
    {
        $validator = Validator::make($request->all(), [
            'requiredPassword' => 'nullable|string', //nullable is needed for empty field
        ]);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }
        $fileSetting = FilesSettings::where('uid', '=', $given_uid)->where('type','=',1)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        if ($request->requiredPassword !== $fileSetting->password){
            return response()->json(['message' => 'Bad Password'], 404);
        }
        
        $data = $this->fetchData(Securetext::class, $given_uid);
        if ($data && $this->checkBlock($data)) {
            return $this->blockErrorResponse();
        }

        if ($given_uid) {
            $this->deleteBurnAfterRead($given_uid);
        }

        return response()->json(['data' => $data], 200);
    }
    

    // Delete Texts
    public function apiDeleteTexts(Request $request, $given_uid = null)
    {
        if (!$given_uid) {
            return response()->json(['message' => 'Terrible Code'], 500);
        }

        $data = $this->fetchData(Securetext::class, $given_uid);

        if ($data && $this->checkBlock($data)) {
            return $this->blockErrorResponse();
        }

        $this->deleteFilesAndSettings($given_uid);

        return response()->json(['message' => 'Texts deleted'], 200);
    }

    // File Upload
    public function apiFileUploadMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filesupload' => 'required|array|min:1',
            'filesupload.*' => 'required|file|max:5120',
            'expiry_date' => 'required|after:today',
            'burn_after_read' => 'required|boolean',
            'uid' => 'unique:files_settings',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $final_uid = $this->ApiControllerCheckUid($request->uid);
        $final_ip = $this->ApiControllerCheckIp($request);

        $storedsettings = $this->storeFileSettings($request, $final_uid, $final_ip, 2);

        foreach ($request->file('filesupload') as $file) {
            $path = $file->store('uploads', 'public');
            Securefile::create([
                'file_detail' => $path,
                'setting_id' => $storedsettings->id,
            ]);
        }

        return response()->json(['message' => 'Files and settings uploaded successfully'], 201);
    }

    public function apiFileUploadSingle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filesupload' => 'required|file|max:5120',
            // 'expiry_date' => 'required|after:today',
            //'file_burn_after_read' => 'required|boolean',
            'uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        // $final_uid = $this->ApiControllerCheckUid($request->uid);
        // $final_ip = $this->ApiControllerCheckIp($request);

        $fileSetting = FilesSettings::where('uid', $request->uid)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'Terrible Code', 'fileSetting' => $fileSetting], 501);
        }
        $path = $request->file('filesupload')->store('uploads', 'public');

        //Generating THumbnail
        $mimeType = $request->file('filesupload')->getMimeType();
        // Check if it's a video or image

        if (strpos($mimeType, 'video/') === 0) {
            $thumbnailPath = $this->generateThumbnail($path);
        } elseif (strpos($mimeType, 'image/') === 0) {
            $thumbnailPath = $path;
        } else {
            // For other file types
            $thumbnailPath = null;
        }

        Securefile::create([
            'file_burn_after_read' => $fileSetting->burn_after_read,
            'file_uid' => str()->random(8),
            'file_detail' => $path,
            'setting_id' => $fileSetting->id,
            'thumbnail' => $thumbnailPath
        ]);

        return response()->json(['message' => 'One File uploaded successfully', 'uid' => $fileSetting->id], 201);

    }
    public function apiCreateSetting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expiry_date' => 'required|regex:/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{3})?Z)$/|after:today',
            'burn_after_read' => 'required|boolean',
            'password' => 'nullable|string', //nullable is needed for empty field
            'ip' => 'required|string',
            //'uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $final_uid = $this->ApiControllerCheckUid($request->uid);
        $final_ip = $this->ApiControllerCheckIp($request);

        $storedsettings = $this->storeFileSettings($request, $final_uid, $final_ip, 2);

        return response()->json(['message' => 'Settings uploaded successfully', 'uid' => $final_uid], 201);
    }

    // Show Files
    public function apiShowMultipleFiles(Request $request, $given_uid = null)
    {
        $validator = Validator::make($request->all(), [
            'requiredPassword' => 'nullable|string', //nullable is needed for empty field
        ]);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $fileSetting = FilesSettings::where('uid', '=', $given_uid)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        if ($request->requiredPassword !== $fileSetting->password){
            return response()->json(['message' => 'Bad Password'], 404);
        }

        if ($fileSetting->burn_after_read > 1) {
            //dd('del');
            $fileSetting->delete();
            return response()->json(['message' => 'UID doesnt exist, burned'], 404);
        }


        $data = $this->fetchData(Securefile::class, $given_uid);

        if ($data && $this->checkBlock($data)) {
            return $this->blockErrorResponse();
        }

        // If Settings burn > 1, update to 2 and also update burn of securefiles
        if ($fileSetting->burn_after_read > 0) {
            $fileSetting->update(['burn_after_read' => $fileSetting->burn_after_read + 1]);
            $fileSetting->securefile->each(function ($securefile){
                $securefile->update(['file_burn_after_read' => 2]);
            });
        }
        // if ($given_uid) {
        //     $this->deleteBurnAfterRead($given_uid);
        // }
        $data->each(function ($item) {
            // Add custom attributes
            $item->file_location = asset('storage/' . $item->file_detail); // Replace with actual logic or value
        });
        foreach ($data as $d) { //Reusable
            $d['file_detail'] = basename($d['file_detail']);
            $d['thumbnail'] = asset('storage/' . $d['thumbnail']);
        }
        return response()->json(['data' => $data], 200);
    }

    public function apiShowOneFile(Request $request, $given_uid)
    {
        $validator = Validator::make($request->all(), [
            'requiredPassword' => 'nullable|string', //nullable is needed for empty field
        ]);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        
        //$data = $this->fetchData(Securefile::class, $given_uid);
        $table = (new Securefile)->getTable();
        $data = Securefile::leftJoin('files_settings', "{$table}.setting_id", '=', 'files_settings.id')
            ->select("{$table}.*", 'files_settings.password', 'files_settings.expiry_date', 'files_settings.burn_after_read', 'files_settings.uid', 'files_settings.ip', 'files_settings.block')
            ->where('file_uid', '=', $given_uid) //no fetchData() bcz file_uid
            ->get();
        if (!$data->count()) {
            return response()->json(['message' => 'FileUID doesnt exist'], 404);
        }

        $fileUID = Securefile::where('file_uid', '=', $given_uid)->first();
        if (!$fileUID) {
            return response()->json(['message' => 'Terrible Code'], 501);
        }
        if ($request->requiredPassword !== $fileUID->files_settings->password){
            return response()->json(['message' => 'Bad Password'], 404);
        }
        if ($fileUID->file_burn_after_read > 1) {
            $fileUID->delete();
            return response()->json(['message' => 'FileUID doesnt exist, burned'], 404);
        }

        if ($data && $this->checkBlock($data)) {
            return $this->blockErrorResponse();
        }
        if ($fileUID->file_burn_after_read > 0) {
            //dd($data);
            $fileUID->update(['file_burn_after_read' => $fileUID->file_burn_after_read + 1]);
        }
        // if ($given_uid) {
        //     $this->deleteBurnAfterRead($given_uid);
        // }
        $data->each(function ($item) {
            // Add custom attributes
            $item->file_location = asset('storage/' . $item->file_detail); // Replace with actual logic or value
        });
        foreach ($data as $d) { //Reusable
            $d['file_detail'] = basename($d['file_detail']);
            $d['thumbnail'] = asset('storage/' . $d['thumbnail']);
        }
        return response()->json(['data' => $data], 200);
    }

    public function apiPreviewFiles(Request $request, $given_uid = null)
    {
        $data = $this->fetchData(Securefile::class, $given_uid);
        //dd($data); //$d['thumbnail']
        $fileids = [];
        $imageUrls = [];
        // if ($data && $this->checkBlock($data)) {
        //     return $this->blockErrorResponse();
        // }
        // if ($given_uid) {
        //     $this->deleteBurnAfterRead($given_uid);
        // }
        foreach ($data as $d) { //Reusable
            //$fileids[] = $d['id'];
            //$imageUrls[] = asset('storage/' . $d['thumbnail']);
            $d['file_detail'] = basename($d['file_detail']);
            $d['thumbnail'] = asset('storage/' . $d['thumbnail']);
        }
        //return response()->json(['fileids' => $fileids, 'images' => $imageUrls], 200);
        return response()->json(['data' => $data], 200);
    }

    public function apiUpdateTitles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:securefile,id',
            'items.*.title' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }
        $updatedItems = [];

        foreach ($request->input('items') as $itemData) {
            $item = Securefile::findOrFail($itemData['id']);
            if ($item) {
                $item->title = $itemData['title'];
                $item->save();
            }

            $updatedItems[] = $item;
        }
        // if ($data && $this->checkBlock($data)) {
        //     return $this->blockErrorResponse();
        // }
        // if ($given_uid) {
        //     $this->deleteBurnAfterRead($given_uid);
        // }
        return response()->json([
            'message' => 'Titles updated successfully',
            'updatedItems' => $updatedItems,
        ]);
    }
    public function apiDownloadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fileid' => 'required|exists:securefile,id'
        ]);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }
        $filer = Securefile::findOrFail($request->fileid);
        //dd(storage_path('app/public/' . $filer['file_detail']));
        if ($filer) {
            return response()->download(storage_path('app/public/' . $filer['file_detail']));
        } else {
            return response()->json(['message' => 'bruh'], 501);
        }
    }

    // Delete Files
    public function apiDeleteMultipleFiles(Request $request, $given_uid = null)
    {
        // $data = $this->fetchData(Securefile::class, $given_uid);
        $data = FilesSettings::where('uid', $given_uid)->first();
        if (!$data) {
            return response()->json(['message' => 'UID not found'], 400);
        }
        if ($data['block']) {
            return $this->blockErrorResponse();
        }
        $this->deleteFilesAndSettings($given_uid);
        return response()->json(['message' => 'Files deleted'], 200);
    }

    public function apiDeleteOnefile(Request $request, $given_uid = null)
    {
        $data = $this->fetchData(Securefile::class, $given_uid);
        $data = Securefile::where('file_uid', $given_uid)->first();
        if (!$data) {
            return response()->json(['message' => 'UID not found'], 400);
        }
        if ($data['block']) {
            return $this->blockErrorResponse();
        }
        $this->deleteFilesAndSettings($given_uid);
        return response()->json(['message' => 'Files deleted'], 200);
    }

    public function apiGetMirrorsExpiry(Request $request)
    {
        $securemirrors = Securemirror::get();
        $expirationduration = Expirationduration::get();
        return response()->json(['mirror' => $securemirrors, 'expire' => $expirationduration], 200);
    }

    // Protected Functions for Reusability
    protected function ApiControllerCheckUid($uid)
    {
        return $uid ?: str()->random(8);
    }

    protected function ApiControllerCheckIp(Request $request)
    {
        return $request->ip ?: $request->ip();
    }

    protected function checkBlock($data)
    {
        return $data->count() && $data[0]->block;
    }

    protected function generateThumbnail($filePath)
    {
        $dir = storage_path('app/public/uploads/thumbnails');

        // Check if the directory exists
        if (!File::exists($dir)) {
            // Create the directory if it doesn't exist
            File::makeDirectory($dir, 0755, true);
        }

        $ffmpeg = FFMpeg::create();
        $video = $ffmpeg->open(storage_path('app/public/' . $filePath));

        // Generate a thumbnail at the 1-second mark
        $thumbnailPath = 'uploads/thumbnails/' . pathinfo($filePath, PATHINFO_FILENAME) . '.jpg';
        //dd(storage_path('app/public/'.$thumbnailPath));
        $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))->save(storage_path('app/public/' . $thumbnailPath));

        return $thumbnailPath;
    }

    protected function validationErrorResponse($validator)
    {
        return response()->json([
            'status' => false,
            'message' => "Validation Error",
            'errors' => $validator->errors()->all(),
        ], 401);
    }

    protected function blockErrorResponse()
    {
        return response()->json(['message' => 'UID Blocked'], 403);
    }

    protected function storeFileSettings($request, $uid, $ip, $type)
    {
        $fileSetting = FilesSettings::where('uid', $uid)->first();
        if ($fileSetting) {
            return $fileSetting;
        }
        if ($request->password) {
            return FilesSettings::create([
                'expiry_date' => strtotime($request->expiry_date),
                'burn_after_read' => $request->burn_after_read,
                'password' => $request->password,
                'uid' => $uid,
                'ip' => $ip,
                'type' => $type,
            ]);
        } else {
            return FilesSettings::create([
                'expiry_date' => strtotime($request->expiry_date),
                'burn_after_read' => $request->burn_after_read,
                'uid' => $uid,
                'ip' => $ip,
                'type' => $type,
            ]);
        }
    }

    protected function fetchData($model, $uid)
    {
        $table = (new $model)->getTable(); // Get the table name dynamically

        return $uid
            ? $model::leftJoin('files_settings', "{$table}.setting_id", '=', 'files_settings.id')
                ->select("{$table}.*", 'files_settings.password', 'files_settings.expiry_date', 'files_settings.burn_after_read', 'files_settings.uid', 'files_settings.ip', 'files_settings.block')
                ->where('uid', '=', $uid)
                ->get()
            : $model::leftJoin('files_settings', "{$table}.setting_id", '=', 'files_settings.id')
                ->select("{$table}.*", 'files_settings.password', 'files_settings.expiry_date', 'files_settings.burn_after_read', 'files_settings.uid', 'files_settings.ip', 'files_settings.block')
                ->get();
    }

    protected function deleteBurnAfterRead($uid)
    {
        DB::table('files_settings')
            ->where('uid', '=', $uid)
            ->where('burn_after_read', '=', 1)
            ->delete();
    }

    protected function deleteFilesAndSettings($uid)
    {
        $tablename = (new Securefile)->getTable();
        //dd($uid,$tablename);
        $toBedeleted = FilesSettings::rightJoin("{$tablename}", "{$tablename}.setting_id", '=', 'files_settings.id')->where('uid', $uid)->get();
        //dd($toBedeleted);
        foreach ($toBedeleted as $d) {
            if (isset($d['file_detail']) && Storage::disk('public')->exists($d['file_detail'])) {
                Storage::disk('public')->delete($d['file_detail']);
            }
            if (isset($d['thumbnail']) && Storage::disk('public')->exists($d['thumbnail'])) {
                Storage::disk('public')->delete($d['thumbnail']);
            }
        }

        FilesSettings::where('uid', '=', $uid)->delete();
    }
}
