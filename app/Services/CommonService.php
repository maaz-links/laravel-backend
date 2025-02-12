<?php

namespace App\Services;

use App\Models\FilesSettings;
use App\Models\Securefile;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Storage;
class CommonService
{
    public function CheckBurn($myUID, $singleFile = 0)
    {
        if ($singleFile) {
            if ($myUID->file_burn_after_read > 1) { //If one file is set to burn and already read.
                $myUID->delete();
                return response()->json(['message' => 'FileUID burned'], 406);
            }
            if ($myUID->files_settings->expiry_date < time()) { //if expiry date exceeds current time
                //$myUID->delete();
                $myUID->files_settings->delete();
                return response()->json(['message' => 'FileUID and Settings expired'], 406);
            }
        } else {
            if ($myUID->burn_after_read > 1) { //If setting is set to burn and already read
                $myUID->delete();
                return response()->json(['message' => 'SettingUID burned'], 406);
            }
            if ($myUID->expiry_date < time()) {//If expiry date exceeds current time
                $myUID->delete();
                return response()->json(['message' => 'SettingUID expired'], 406);
            }
            //dd($myUID->securefile()->count() ,$myUID->securetext()->count());
            if (!$myUID->securefile()->count() && !$myUID->securetext()->count()) { //If settings is empty
                $myUID->delete();
                //dd('damnit');
                return response()->json(['message' => 'SettingUID was empty'], 406);
            }
            //dd('nigg');
            $myUID->securefile->each(function ($securefile) {
                if ($securefile->file_burn_after_read > 1) { //Edge case where setting is not read but files within are already read individually
                    $securefile->delete();
                }
            });
        }
        return false;
    }

    // Protected Functions for Reusability
    public function ApiControllerCheckUid($uid)
    //This creates 8 char random string, if uid is not passed in request
    {
        return $uid ?: str()->random(8);
    }

    public function ApiControllerCheckIp(Request $request)
    //This returns string "127.0.0.1" if ip is not given in request
    //ip stores the domain of mirror that is selected in frontend
    //Therefore ip is required when submitting data to backend.
    //This function was made when ip was considered optional.
    {
        return $request->ip ?: $request->ip();
    }

    public function validationErrorResponse($validator)
    {
        return response()->json([
            'status' => false,
            'message' => "Validation Error",
            'errors' => $validator->errors()->all(),
        ], 401);
    }

    public function blockErrorResponse()
    {
        return response()->json(['message' => 'UID Blocked'], 403);
    }

    public function storeFileSettings($request, $uid, $ip, $type)
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

    public function fetchData($model, $uid, $singleFile = 0)
    //This gives Securefile or Securetext data joined with file_settings
    {
        $table = (new $model)->getTable(); // Get the table name dynamically
        $uidCrit = ($singleFile && ($model == Securefile::class)) ? 'file_uid' : 'uid';
        $data = $uid
            ? $model::leftJoin('files_settings', "{$table}.setting_id", '=', 'files_settings.id')
                ->select("{$table}.*", 'files_settings.expiry_date', 'files_settings.burn_after_read', 'files_settings.uid', 'files_settings.ip', 'files_settings.block')
                ->where("{$uidCrit}", '=', $uid)
                ->get()
            : $model::leftJoin('files_settings', "{$table}.setting_id", '=', 'files_settings.id')
                ->select("{$table}.*", 'files_settings.expiry_date', 'files_settings.burn_after_read', 'files_settings.uid', 'files_settings.ip', 'files_settings.block')
                ->get();

        //File detail will contain actual filename with extension
        //thumbnail will contain full path to thumbnail image in backend
        //file_location will contain full path to actual file in backend
        if ($model == Securefile::class) {
            $data->each(function ($item) {

                $filePath = $item->file_detail;
                //Modify existing attrib
                $item->file_detail = basename($item->file_detail);
                if ($item->thumbnail) {
                    if ($filePath == $item->thumbnail) {
                        $item->thumbnail = url('api/files/' . $item->file_detail);
                    } else {
                        $item->thumbnail = url('api/thumbnails/' . $item->file_detail);
                    }
                }
                $item->file_location = url('api/files/' . $item->file_detail);
                $item->mime = Storage::disk('local')->mimeType($filePath);
                $item->size = Storage::disk('local')->size($filePath);
                $item->extension = pathinfo(Storage::disk('local')->path($filePath), PATHINFO_EXTENSION);
            });
        }
        return $data;
    }

}
