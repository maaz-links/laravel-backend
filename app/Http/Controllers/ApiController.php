<?php

namespace App\Http\Controllers;

use App\Models\FilesSettings;
use App\Models\Securefile;
use App\Models\Securetext;
use App\Services\CommonService;
use File;
use Illuminate\Http\Request;
use Storage;
use Validator;
use FFMpeg\FFMpeg;
class ApiController extends Controller
{
    protected $apiService;

    public function __construct(CommonService $apiService)
    {
        $this->apiService = $apiService;
    }

    // File Upload
    public function apiFileUploadMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filesupload' => 'required|array|min:1',
            'filesupload.*' => 'required|file',
            'expiry_date' => 'required|after:today',
            'burn_after_read' => 'required|boolean',
            'uid' => 'unique:files_settings',
        ]);
        if ($validator->fails()) {
            return $this->apiService->validationErrorResponse($validator);
        }

        $final_uid = $this->apiService->ApiControllerCheckUid($request->uid);
        $final_ip = $this->apiService->ApiControllerCheckIp($request);

        $storedsettings = $this->apiService->storeFileSettings($request, $final_uid, $final_ip, 2);

        foreach ($request->file('filesupload') as $file) {
            $path = $file->store('uploads', 'local');
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
            'filesupload' => 'required|file',
            //'file_burn_after_read' => 'required|boolean',
            'uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->apiService->validationErrorResponse($validator);
        }
        $fileSetting = FilesSettings::where('uid', $request->uid)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'Terrible Upload Handling', 'fileSetting' => $fileSetting], 501);
        }
        //$path = $request->file('filesupload')->store('uploads', 'local');
        $file = $request->file('filesupload');
        $originalExtension = $file->getClientOriginalExtension(); //Ensure that original extension is retained
        $filename = $file->hashName(); // Laravel's default unique filename
        // Append the original extension
        $filenameWithExtension = pathinfo($filename, PATHINFO_FILENAME) . '.' . $originalExtension;
        $path = $file->storeAs('uploads', $filenameWithExtension, 'local');
        
        //getFileName
        $fileNameWithExtension = $request->file('filesupload')->getClientOriginalName(); // e.g., example.txt
        $fileNameWithoutExtension = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

        //Generating THumbnail
        $mimeType = $request->file('filesupload')->getMimeType();
        // Check if it's a video or image

        $thumbnailerror = false;
        if (strpos($mimeType, 'video/') === 0) {
            try{
                $thumbnailPath = $this->generateThumbnail($path);
            } catch (\Throwable $e) {
                $thumbnailPath = null;
                $thumbnailerror = true;                
            }
        } elseif (strpos($mimeType, 'image/') === 0) {
            $thumbnailPath = $path;
        } else {
            // For other file types
            $thumbnailPath = null;
        }
        Securefile::create([
            'file_burn_after_read' => $fileSetting->burn_after_read,
            'title' => $fileNameWithoutExtension,
            'file_uid' => str()->random(8),
            'file_detail' => $path,
            'setting_id' => $fileSetting->id,
            'thumbnail' => $thumbnailPath
        ]);
        if($thumbnailerror){
            return response()->json(['message' => 'Thumbnail didnt generate', 'setting_uid' => $fileSetting->uid], 206);
        }
        return response()->json(['message' => 'One File uploaded successfully', 'setting_uid' => $fileSetting->uid], 201);

    }

    public function apiUpdateOneFile(Request $request, $given_uid = null)
    {
        $olddata = Securefile::where('file_uid', $given_uid)->first();
        if (!$olddata) {
            return response()->json(['message' => 'UID not found'], 400);
        }
        $oldFile = $olddata->file_detail;
        $oldThumbnail = $olddata->thumbnail;
        
        $validator = Validator::make($request->all(), [
            'filesupload' => 'required|file',
            //'file_burn_after_read' => 'required|boolean',
            'uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->apiService->validationErrorResponse($validator);
        }

        $fileSetting = FilesSettings::where('uid', $request->uid)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'Terrible Upload Handling', 'fileSetting' => $fileSetting], 501);
        }
        //$path = $request->file('filesupload')->store('uploads', 'local');
        $file = $request->file('filesupload');
        $originalExtension = $file->getClientOriginalExtension(); //Ensure that original extension is retained
        $filename = $file->hashName(); // Laravel's default unique filename
        // Append the original extension
        $filenameWithExtension = pathinfo($filename, PATHINFO_FILENAME) . '.' . $originalExtension;
        $path = $file->storeAs('uploads', $filenameWithExtension, 'local');

        //getFileName
        $fileNameWithExtension = $request->file('filesupload')->getClientOriginalName(); // e.g., example.txt
        $fileNameWithoutExtension = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

        //Generating THumbnail
        $mimeType = $request->file('filesupload')->getMimeType();
        // Check if it's a video or image

        $thumbnailerror = false;
        if (strpos($mimeType, 'video/') === 0) {
            try{
                $thumbnailPath = $this->generateThumbnail($path);
            } catch (\Throwable $e) {
                $thumbnailPath = null;
                $thumbnailerror = true;                
            }
            
        } elseif (strpos($mimeType, 'image/') === 0) {
            $thumbnailPath = $path;
        } else {
            // For other file types
            $thumbnailPath = null;
        }
        $olddata->update([
            //'file_burn_after_read' => $fileSetting->burn_after_read,
            'title' => $fileNameWithoutExtension,
            //'file_uid' => str()->random(8),
            'file_detail' => $path,
            //'setting_id' => $fileSetting->id,
            'thumbnail' => $thumbnailPath
        ]);
        //Delete old data after updating
        if ($oldFile) {
            if (Storage::disk('local')->exists($oldFile)) {
                Storage::disk('local')->delete($oldFile);
            }
        }
        if ($oldThumbnail) {
            if (Storage::disk('local')->exists($oldThumbnail)) {
                Storage::disk('local')->delete($oldThumbnail);
            }
        }
        if($thumbnailerror){
            return response()->json(['message' => 'Thumbnail didnt generate', 'setting_uid' => $fileSetting->uid], 206);
        }
        return response()->json(['message' => 'One File uploaded successfully', 'setting_uid' => $fileSetting->uid], 201);

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
            return $this->apiService->validationErrorResponse($validator);
        }

        $final_uid = $this->apiService->ApiControllerCheckUid($request->uid);
        $final_ip = $this->apiService->ApiControllerCheckIp($request);

        $storedsettings = $this->apiService->storeFileSettings($request, $final_uid, $final_ip, 2); // 2 = Files, 1 = Text

        return response()->json(['message' => 'Settings uploaded successfully', 'uid' => $final_uid], 201);
    }

    // Show Files
    public function apiShowMultipleFiles(Request $request, $given_uid = null)
    {
        $validator = Validator::make($request->all(), [
            'requiredPassword' => 'nullable|string', //nullable is needed for empty field
        ]);
        if ($validator->fails()) {
            return $this->apiService->validationErrorResponse($validator);
        }

        $fileSetting = FilesSettings::where('uid', '=', $given_uid)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        if ($request->requiredPassword !== $fileSetting->password) {
            return response()->json(['message' => 'Bad Password'], 404);
        }

        //Burn record if file UID with burn after read enabled is called the second time. Files from storage are deleted via Model Events
        //Also Securefile and FilesSettings table have 'cascade' foreign key constraint
        // (If Settings is deleted, then the records that reference it are also deleted)
        if ($fileSetting->burn_after_read > 1) {
            //If burn > 1, trigger delete
            $fileSetting->delete();
            return response()->json(['message' => 'UID doesnt exist, burned'], 404);
        }


        $data = $this->apiService->fetchData(Securefile::class, $given_uid);

        // If Settings burn is 1, update to 2 and also update burn of securefiles
        if ($fileSetting->burn_after_read > 0) {
            $fileSetting->update(['burn_after_read' => 2]);
            $fileSetting->securefile->each(function ($securefile) {
                $securefile->update(['file_burn_after_read' => 2]);
            });
        }

        return response()->json(['data' => $data], 200);
    }

    public function apiShowOneFile(Request $request, $given_uid)
    {
        $validator = Validator::make($request->all(), [
            'requiredPassword' => 'nullable|string', //nullable is needed for empty field
        ]);
        if ($validator->fails()) {
            return $this->apiService->validationErrorResponse($validator);
        }

        $fileUID = Securefile::where('file_uid', '=', $given_uid)->first();
        if (!$fileUID) {
            return response()->json(['message' => 'FileUID doesnt exist'], 404);
        }
        if ($request->requiredPassword !== $fileUID->files_settings->password) {
            return response()->json(['message' => 'Bad Password'], 404);
        }
        if ($fileUID->file_burn_after_read > 1) {
            $fileUID->delete();
            return response()->json(['message' => 'FileUID doesnt exist, burned'], 404);
        }

        $data = $this->apiService->fetchData(Securefile::class, $given_uid, 1);

        if ($fileUID->file_burn_after_read > 0) {
            $fileUID->update(['file_burn_after_read' => $fileUID->file_burn_after_read + 1]);
        }

        return response()->json(['data' => $data], 200);
    }

    public function apiIsPassRequiredSetting(Request $request,$given_uid){
        $fileSetting = FilesSettings::where('uid', '=', $given_uid)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        $isBurned = $this->apiService->CheckBurn($fileSetting,0);
        if ($isBurned) {return $isBurned;}
        if ($fileSetting->block) {return $this->apiService->blockErrorResponse();}

        return response()->json([
            'message' => $fileSetting->password ? 'true' : 'false',
            'settinguid' => $fileSetting->uid,
            'expiry' => $fileSetting->expiry_date,
            'burn_after_read' => $fileSetting->burn_after_read,
            'type' => $fileSetting->type,
        ], 200);
    }

    public function apiIsPassRequiredSingleFile(Request $request,$given_uid){
        $fileUID = Securefile::where('file_uid', '=', $given_uid)->first();
        if (!$fileUID) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        
        $isBurned = $this->apiService->CheckBurn($fileUID,1);
        if ($isBurned) {return $isBurned;}
        if ($fileUID->files_settings->block) {return $this->apiService->blockErrorResponse();}

        return response()->json([
            'message' => $fileUID->files_settings->password ? 'true' : 'false',
            'settinguid' => $fileUID->files_settings->uid,
            'expiry' => $fileUID->files_settings->expiry_date,
            'burn_after_read' => $fileUID->file_burn_after_read,
            'type' => $fileUID->files_settings->type,
        ], 200);
        
    }

    public function apiVerifyPassword(Request $request, $given_uid){
        $validator = Validator::make($request->all(), [
            'requiredPassword' => 'nullable|string', //nullable is needed for empty field
        ]);
        if ($validator->fails()) {
            return $this->apiService->validationErrorResponse($validator);
        }
        $fileSetting = FilesSettings::where('uid', '=', $given_uid)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        if ($request->requiredPassword !== $fileSetting->password) {
            return response()->json(['message' => 'Bad Password'], 404);
        }else{
            return response()->json(['message' => 'OK'], 200);
        }
    }

    public function apiPreview(Request $request, $given_uid = null)
    {
        $fileSetting = FilesSettings::where('uid', '=', $given_uid)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        $data = [];
        switch ($fileSetting->type) {
            case 1:
                $data = $this->apiService->fetchData(Securetext::class, $given_uid);
                break;
            case 2:
                $data = $this->apiService->fetchData(Securefile::class, $given_uid);
                break;
            default:
                # code...
                break;
        }
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
            return $this->apiService->validationErrorResponse($validator);
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
            return $this->apiService->validationErrorResponse($validator);
        }
        $filer = Securefile::findOrFail($request->fileid);
        if ($filer) {
            return response()->download(storage_path('app/private/' . $filer['file_detail']));
        } else {
            return response()->json(['message' => 'bruh'], 501);
        }
    }

    // Delete Files
    public function apiDeleteMultipleFiles(Request $request, $given_uid = null)
    {
        $data = FilesSettings::where('uid', $given_uid)->where('type','=',2)->first();
        if (!$data) {
            return response()->json(['message' => 'UID not found'], 400);
        }
        if ($data['block']) {
            return $this->apiService->blockErrorResponse();
        }
        $data->delete();
        return response()->json(['message' => 'Files deleted'], 200);
    }

    public function apiDeleteOnefile(Request $request, $given_uid = null)
    {
        $data = Securefile::where('file_uid', $given_uid)->first();
        if (!$data) {
            return response()->json(['message' => 'UID not found'], 400);
        }
        if ($data['block']) {
            return $this->apiService->blockErrorResponse();
        }
        $data->delete();
        return response()->json(['message' => 'Files deleted'], 200);
    }

    protected function generateThumbnail($filePath)
    {
        $dir = storage_path('app/private/uploads/thumbnails');

        // Check if the directory exists
        if (!File::exists($dir)) {
            // Create the directory if it doesn't exist
            File::makeDirectory($dir, 0755, true);
        }

        $ffmpeg = FFMpeg::create();
        // $ffmpeg = FFMpeg::create([
        //     'ffmpeg.binaries'  => '/var/www/vhosts/filepad.forum-solution.com/httpdocs/ffmpeg',
        //     'ffprobe.binaries' => '/var/www/vhosts/filepad.forum-solution.com/httpdocs/ffprobe',
        // ]);
        $video = $ffmpeg->open(storage_path('app/private/' . $filePath));

        // Generate a thumbnail at the 1-second mark
        $thumbnailPath = 'uploads/thumbnails/' . pathinfo($filePath, PATHINFO_FILENAME) . '.jpg';
        $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))->save(storage_path('app/private/' . $thumbnailPath));

        return $thumbnailPath;
    }

}
