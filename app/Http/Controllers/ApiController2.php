<?php

namespace App\Http\Controllers;

use App\Models\FilesSettings;
use App\Models\Securefile;
use App\Models\Securetext;
use App\Models\Securemirror;
use App\Models\Expirationduration;
use App\Services\CommonService;
use DB;
use File;
use Illuminate\Http\Request;
use Storage;
use Validator;
use FFMpeg\FFMpeg;
class ApiController2 extends Controller
{
    protected $apiService;

    public function __construct(CommonService $apiService)
    {
        $this->apiService = $apiService;
    }
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

        //Remember, files are encrypted and decrypted in Securetext Model
        Securetext::create([
            'content' => $request->textupload,
            'setting_id' => $storedsettings->id,
        ]);

        return response()->json(['message' => 'Text uploaded successfully', 'uid' => $final_uid], 201);
    }

    // Show Texts
    public function apiShowTexts(Request $request, $given_uid)
    {
        //Validate password string and check if record for given UID exists
        $validator = Validator::make($request->all(), [
            'requiredPassword' => 'nullable|string', //nullable is needed for empty field, dont use required
        ]);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }
        $fileSetting = FilesSettings::where('uid', '=', $given_uid)->where('type', '=', 1)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        if ($fileSetting->block) {return $this->blockErrorResponse();}
        if ($request->requiredPassword !== $fileSetting->password) {
            return response()->json(['message' => 'Bad Password'], 404);
        }

        $data = $this->fetchData(Securetext::class, $given_uid);
        // if ($data && $this->checkBlock($data)) {
        //     return $this->blockErrorResponse();
        // }

        //Burn record if burn_after_read is 1
        if($fileSetting->burn_after_read){
            $fileSetting->delete();
        }

        return response()->json(['data' => $data], 200);
    }


    // Delete Texts
    public function apiDeleteTexts(Request $request, $given_uid)
    {
        $FileSetting = FilesSettings::where('uid', '=', $given_uid)->first();
        if (!$FileSetting) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        if ($FileSetting->block) {return $this->blockErrorResponse();}

        // $data = $this->fetchData(Securetext::class, $given_uid);

        // if ($data && $this->checkBlock($data)) {
        //     return $this->blockErrorResponse();
        // }
        $FileSetting->delete();
        return response()->json(['message' => 'Texts deleted'], 200);
        
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
            return $this->validationErrorResponse($validator);
        }

        $final_uid = $this->ApiControllerCheckUid($request->uid);
        $final_ip = $this->ApiControllerCheckIp($request);

        $storedsettings = $this->storeFileSettings($request, $final_uid, $final_ip, 2);

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
            return $this->validationErrorResponse($validator);
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
            return $this->validationErrorResponse($validator);
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
            return $this->validationErrorResponse($validator);
        }

        $final_uid = $this->ApiControllerCheckUid($request->uid);
        $final_ip = $this->ApiControllerCheckIp($request);

        $storedsettings = $this->storeFileSettings($request, $final_uid, $final_ip, 2); // 2 = Files, 1 = Text

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


        $data = $this->fetchData(Securefile::class, $given_uid);

        // If Settings burn is 1, update to 2 and also update burn of securefiles
        if ($fileSetting->burn_after_read > 0) {
            $fileSetting->update(['burn_after_read' => 2]);
            $fileSetting->securefile->each(function ($securefile) {
                $securefile->update(['file_burn_after_read' => 2]);
            });
        }


        //File detail will contain actual filename with extension
        //thumbnail will contain full path to thumbnail image in backend
        //file_location will contain full path to actual file in backend
        $data->each(function ($item) {

            $filePath = $item->file_detail;
            //Modify existing attrib
            $item->file_detail = basename($item->file_detail);
            if($item->thumbnail){
                if($filePath == $item->thumbnail){
                    $item->thumbnail = url('api/files/'.$item->file_detail);
                }else{
                    $item->thumbnail = url('api/thumbnails/'.$item->file_detail);
                }
            }
            $item->file_location = url('api/files/'.$item->file_detail);
            $item->mime = Storage::disk('local')->mimeType($filePath);
            $item->size = Storage::disk('local')->size($filePath);
            $item->extension = pathinfo(Storage::disk('local')->path($filePath), PATHINFO_EXTENSION);
        });
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
            ->where('file_uid', '=', $given_uid) //no $this->fetchData() bcz file_uid
            ->get();
        if (!$data->count()) {
            return response()->json(['message' => 'FileUID doesnt exist'], 404);
        }

        $fileUID = Securefile::where('file_uid', '=', $given_uid)->first();
        if (!$fileUID) {
            return response()->json(['message' => 'Terrible Code'], 501);
        }
        if ($request->requiredPassword !== $fileUID->files_settings->password) {
            return response()->json(['message' => 'Bad Password'], 404);
        }
        if ($fileUID->file_burn_after_read > 1) {
            $fileUID->delete();
            return response()->json(['message' => 'FileUID doesnt exist, burned'], 404);
        }

        if ($fileUID->file_burn_after_read > 0) {
            $fileUID->update(['file_burn_after_read' => $fileUID->file_burn_after_read + 1]);
        }

        //File detail will contain actual filename with extension
        //thumbnail will contain full path to thumbnail image in backend
        //file_location will contain full path to actual file in backend
        $data->each(function ($item) {

            $filePath = $item->file_detail;
            //Modify existing attrib
            $item->file_detail = basename($item->file_detail);
            if($item->thumbnail){
                if($filePath == $item->thumbnail){
                    $item->thumbnail = url('api/files/'.$item->file_detail);
                }else{
                    $item->thumbnail = url('api/thumbnails/'.$item->file_detail);
                }
            }
            //Add new attrib
            $item->file_location = url('api/files/'.$item->file_detail);
            $item->mime = Storage::disk('local')->mimeType($filePath);
            $item->size = Storage::disk('local')->size($filePath);
            $item->extension = pathinfo(Storage::disk('local')->path($filePath), PATHINFO_EXTENSION);
        });
        return response()->json(['data' => $data], 200);
    }

    public function apiIsPassRequiredSetting(Request $request,$given_uid){
        $fileSetting = FilesSettings::where('uid', '=', $given_uid)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        $isBurned = $this->CheckBurn($fileSetting,0);
        if ($isBurned) {return $isBurned;}
        if ($fileSetting->block) {return $this->blockErrorResponse();}

        return response()->json([
            'message' => $fileSetting->password ? 'true' : 'false',
            'settinguid' => $fileSetting->uid,
            'expiry' => $fileSetting->expiry_date,
            'burn_after_read' => $fileSetting->burn_after_read,
        ], 200);
    }

    public function apiIsPassRequiredSingleFile(Request $request,$given_uid){
        $fileUID = Securefile::where('file_uid', '=', $given_uid)->first();
        if (!$fileUID) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        
        $isBurned = $this->CheckBurn($fileUID,1);
        if ($isBurned) {return $isBurned;}
        if ($fileUID->files_settings->block) {return $this->blockErrorResponse();}

        return response()->json([
            'message' => $fileUID->files_settings->password ? 'true' : 'false',
            'settinguid' => $fileUID->files_settings->uid,
            'expiry' => $fileUID->files_settings->expiry_date,
            'burn_after_read' => $fileUID->file_burn_after_read,
        ], 200);
        
    }

    public function apiVerifyPassword(Request $request, $given_uid){
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
        if ($request->requiredPassword !== $fileSetting->password) {
            return response()->json(['message' => 'Bad Password'], 404);
        }else{
            return response()->json(['message' => 'OK'], 200);
        }
    }

    public function apiPreviewFiles(Request $request, $given_uid = null)
    {
        $data = $this->fetchData(Securefile::class, $given_uid);

        $data->each(function ($item) {

            $filePath = $item->file_detail;
            //Modify existing attrib
            $item->file_detail = basename($item->file_detail);
            if($item->thumbnail){
                if($filePath == $item->thumbnail){
                    $item->thumbnail = url('api/files/'.$item->file_detail);
                }else{
                    $item->thumbnail = url('api/thumbnails/'.$item->file_detail);
                }
            }

            //Add new attrib
            $item->file_location = url('api/files/'.$item->file_detail);
            $item->mime = Storage::disk('local')->mimeType($filePath);
            $item->size = Storage::disk('local')->size($filePath);
            $item->extension = pathinfo(Storage::disk('local')->path($filePath), PATHINFO_EXTENSION);
        });

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
            return response()->download(storage_path('app/private/' . $filer['file_detail']));
        } else {
            return response()->json(['message' => 'bruh'], 501);
        }
    }

    // Delete Files
    public function apiDeleteMultipleFiles(Request $request, $given_uid = null)
    {
        // $data = $this->fetchData(Securefile::class, $given_uid);
        $data = FilesSettings::where('uid', $given_uid)->where('type','=',2)->first();
        if (!$data) {
            return response()->json(['message' => 'UID not found'], 400);
        }
        if ($data['block']) {
            return $this->blockErrorResponse();
        }
        $data->delete();
        //$this->deleteFilesAndSettings($given_uid);
        return response()->json(['message' => 'Files deleted'], 200);
    }

    public function apiDeleteOnefile(Request $request, $given_uid = null)
    {
        //$data = $this->fetchData(Securefile::class, $given_uid);
        $data = Securefile::where('file_uid', $given_uid)->first();
        if (!$data) {
            return response()->json(['message' => 'UID not found'], 400);
        }
        if ($data['block']) {
            return $this->blockErrorResponse();
        }
        //$this->deleteFilesAndSettings($given_uid);
        $data->delete();
        return response()->json(['message' => 'Files deleted'], 200);
    }

    public function apiGetMirrorsExpiry(Request $request)
    {
        $securemirrors = Securemirror::get();
        $expirationduration = Expirationduration::get();
        return response()->json(['mirror' => $securemirrors, 'expire' => $expirationduration], 200);
    }

    protected function CheckBurn($myUID,$oneFile = 0){
        if($oneFile){
            if ($myUID->file_burn_after_read > 1) { //If one file is set to burn and already read.
                $myUID->delete();
                return response()->json(['message' => 'FileUID burned'], 406);
            }
            if ($myUID->files_settings->expiry_date < time()) { //if expiry date exceeds current time
                //$myUID->delete();
                $myUID->files_settings->delete();
                return response()->json(['message' => 'FileUID and Settings expired'], 406);
            }
        }else{
            if ($myUID->burn_after_read > 1) { //If setting is set to burn and already read
                $myUID->delete();
                return response()->json(['message' => 'SettingUID burned'], 406);
            }
            if ($myUID->expiry_date < time()) {//If expiry date exceeds current time
                $myUID->delete();
                return response()->json(['message' => 'SettingUID expired'], 406);
            }
            //dd($myUID->securefile()->count() ,$myUID->securetext()->count());
            if(!$myUID->securefile()->count() && !$myUID->securetext()->count()){ //If settings is empty
                $myUID->delete();
                //dd('damnit');
                return response()->json(['message' => 'SettingUID was empty'], 406);
            }
            //dd('nigg');
            $myUID->securefile->each(function ($securefile) {
                if($securefile->file_burn_after_read > 1){ //Edge case where setting is not read but files within are already read individually
                    $securefile->delete();
                }
            });
        }
        return false;
    }

    // Protected Functions for Reusability
    protected function ApiControllerCheckUid($uid)
    //This creates 8 char random string, if uid is not passed in request
    {
        return $uid ?: str()->random(8);
    }

    protected function ApiControllerCheckIp(Request $request)
    //This returns string "127.0.0.1" if ip is not given in request
    //ip stores the domain of mirror that is selected in frontend
    //Therefore ip is required when submitting data to backend.
    //This function was made when ip was considered optional.
    {
        return $request->ip ?: $request->ip();
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
        //Request must contain expirydate and burn after read.
        //  Remember type value: 1 = Text, 2 = File(s)

        //Check if uid already exists
        $fileSetting = FilesSettings::where('uid', $uid)->first();
        if ($fileSetting) {
            return $fileSetting;
        }
        return FilesSettings::create([
            'expiry_date' => strtotime($request->expiry_date),
            'burn_after_read' => $request->burn_after_read,
            'password' => $request->password,
            'uid' => $uid,
            'ip' => $ip,
            'type' => $type,
        ]);
    }

    protected function fetchData($model, $uid)
    //This gives Securefile or Securetext data joined with file_settings
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

    public function apiCleardata(Request $request,$limit = 100){
        $allSettings = FilesSettings::take($limit)->get();
        $success = 0;
        $failed = 0;
        foreach ($allSettings as $s) {
            try{
                $s->delete();
                $success++;
            }
            catch(\Throwable $e){
                $failed++;
            }
        }
        return response()->json(['success' => $success,'failed' => $failed], 200);
    }
    
}
