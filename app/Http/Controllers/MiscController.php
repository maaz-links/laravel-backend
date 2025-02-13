<?php

namespace App\Http\Controllers;

use App\Models\Expirationduration;
use App\Models\FilesSettings;
use App\Models\Securemirror;
use Illuminate\Http\Request;

class MiscController extends Controller
{
    public function BringFile($filename, Request $request)
    {
        $path = storage_path("app/private/uploads/{$filename}");
        if (!file_exists($path)) {
            abort(404, "File not found.");
        }
        return response()->file($path);
    }

    public function BringThumbnail($filename, Request $request)
    {
        $directory = storage_path("app/private/uploads/thumbnails/");
        $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

        // Scan the directory for matching files (any extension)
        $matchingFiles = glob("{$directory}{$filenameWithoutExt}.*");
        if (empty($matchingFiles)) {
            abort(404, "File not found.");
        }
        // Get the first matching file
        $path = $matchingFiles[0];
        return response()->file($path);
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

    public function apiGetMirrorsExpiry(Request $request)
    {
        $securemirrors = Securemirror::get();
        $expirationduration = Expirationduration::orderBy('duration')->get();
        return response()->json(['mirror' => $securemirrors, 'expire' => $expirationduration], 200);
    }
    
}
