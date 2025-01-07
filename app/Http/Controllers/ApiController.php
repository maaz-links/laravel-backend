<?php

namespace App\Http\Controllers;

use App\Models\FilesSettings;
use App\Models\Securefile;
use App\Models\Securetext;
use DB;
use Illuminate\Http\Request;
use Storage;
use Validator;
class ApiController extends Controller
{
    // Text Upload
    public function apitextupload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'textupload' => 'required|string',
            'expiry_date' => 'required|after:today',
            'burn_after_read' => 'required|boolean',
            'uid' => 'unique:files_settings',
            'ip' => 'string',
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

        return response()->json(['message' => 'Text uploaded successfully'], 201);
    }

    // Show Texts
    public function apishowtexts(Request $request, $given_uid = null)
    {
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
    public function apideletetexts(Request $request, $given_uid = null)
    {
        if (!$given_uid) {
            return response()->json(['message' => 'Terrible Code'], 500);
        }

        $data = $this->fetchData(Securetext::class, $given_uid);

        if ($data && $this->checkBlock($data)) {
            return $this->blockErrorResponse();
        }

        $this->deleteFilesAndSettings($data, $given_uid);

        return response()->json(['message' => 'Texts deleted'], 200);
    }

    // File Upload
    public function apifileupload(Request $request)
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

    public function apifileuploadsingle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filesupload' => 'required|file|max:5120',
            'expiry_date' => 'required|after:today',
            'burn_after_read' => 'required|boolean',
            'uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $final_uid = $this->ApiControllerCheckUid($request->uid);
        $final_ip = $this->ApiControllerCheckIp($request);

        $storedsettings = $this->storeFileSettings($request, $final_uid, $final_ip, 2);

            $path = $request->file('filesupload')->store('uploads', 'public');
            Securefile::create([
                'file_detail' => $path,
                'setting_id' => $storedsettings->id,
            ]);

        return response()->json(['message' => 'One File uploaded successfully', 'uid' => $final_uid], 201);
    }

    // Show Files
    public function apishowfiles(Request $request, $given_uid = null)
    {
        $data = $this->fetchData(Securefile::class, $given_uid);

        if ($data && $this->checkBlock($data)) {
            return $this->blockErrorResponse();
        }

        if ($given_uid) {
            $this->deleteBurnAfterRead($given_uid);
        }

        return response()->json(['data' => $data], 200);
    }

    // Delete Files
    public function apideletefiles(Request $request, $given_uid = null)
    {
        if (!$given_uid) {
            return response()->json(['message' => 'Terrible Code'], 500);
        }

        $data = $this->fetchData(Securefile::class, $given_uid);

        if ($data && $this->checkBlock($data)) {
            return $this->blockErrorResponse();
        }

        $this->deleteFilesAndSettings($data, $given_uid);

        return response()->json(['message' => 'Files deleted'], 200);
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

        return FilesSettings::create([
            'expiry_date' => strtotime($request->expiry_date),
            'burn_after_read' => $request->burn_after_read,
            'uid' => $uid,
            'ip' => $ip,
            'type' => $type,
        ]);
    }

    protected function fetchData($model, $uid)
{
    $table = (new $model)->getTable(); // Get the table name dynamically

    return $uid
        ? $model::leftJoin('files_settings', "{$table}.setting_id", '=', 'files_settings.id')
            ->select("{$table}.*", 'files_settings.expiry_date', 'files_settings.burn_after_read', 'files_settings.uid', 'files_settings.ip', 'files_settings.block')
            ->where('uid', '=', $uid)
            ->get()
        : $model::leftJoin('files_settings', "{$table}.setting_id", '=', 'files_settings.id')
            ->select("{$table}.*", 'files_settings.expiry_date', 'files_settings.burn_after_read', 'files_settings.uid')
            ->get();
}

    protected function deleteBurnAfterRead($uid)
    {
        DB::table('files_settings')
            ->where('uid', '=', $uid)
            ->where('burn_after_read', '=', 1)
            ->delete();
    }

    protected function deleteFilesAndSettings($data, $uid)
    {
        foreach ($data as $d) {
            if (isset($d['file_detail']) && Storage::disk('public')->exists($d['file_detail'])) {
                Storage::disk('public')->delete($d['file_detail']);
            }
        }

        DB::table('files_settings')->where('uid', '=', $uid)->delete();
    }
}
