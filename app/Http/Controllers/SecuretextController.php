<?php

namespace App\Http\Controllers;

use App\Models\FilesSettings;
use App\Models\Securetext;
use App\Services\CommonService;
use Illuminate\Http\Request;
use Validator;

class SecuretextController extends Controller
{
    protected $apiService;

    public function __construct(CommonService $apiService)
    {
        $this->apiService = $apiService;
    }

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
            return $this->apiService->validationErrorResponse($validator);
        }

        $final_uid = $this->apiService->ApiControllerCheckUid($request->uid);
        $final_ip = $this->apiService->ApiControllerCheckIp($request);

        $storedsettings = $this->apiService->storeFileSettings($request, $final_uid, $final_ip, 1);

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
            return $this->apiService->validationErrorResponse($validator);
        }
        $fileSetting = FilesSettings::where('uid', '=', $given_uid)->where('type', '=', 1)->first();
        if (!$fileSetting) {
            return response()->json(['message' => 'UID doesnt exist'], 404);
        }
        if ($fileSetting->block) {return $this->apiService->blockErrorResponse();}
        if ($request->requiredPassword !== $fileSetting->password) {
            return response()->json(['message' => 'Bad Password'], 404);
        }

        $data = $this->apiService->fetchData(Securetext::class, $given_uid);
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
        if ($FileSetting->block) {return $this->apiService->blockErrorResponse();}

        // $data = $this->fetchData(Securetext::class, $given_uid);

        // if ($data && $this->checkBlock($data)) {
        //     return $this->blockErrorResponse();
        // }
        $FileSetting->delete();
        return response()->json(['message' => 'Texts deleted'], 200);
        
    }
}
