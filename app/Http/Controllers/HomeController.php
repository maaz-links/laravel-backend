<?php

namespace App\Http\Controllers;

use App\Models\FilesSettings;
use App\Models\FilesSettingsView;
use App\Models\Securefile;
use App\Models\Securetext;
use Illuminate\Http\Request;
use Storage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //$settings = FilesSettingsView::all();
        $settings = FilesSettings::all();
        foreach ($settings as $s) {
            if ($s->type == 2) {
                $s->content = 
                Securefile::select('securefile.id', 'securefile.file_detail', 'securefile.thumbnail', 'securefile.file_uid')
                ->where('setting_id', '=', $s->id)->get()->toArray();
            } else if ($s->type == 1) {
                $s->content = 
                Securetext::select('securetext.id', 'securetext.content')
                ->where('setting_id', '=', $s->id)->get()->toArray();
            }
        }
        //dd($settings);
        return view('home', ['settings' => $settings]);
    }

    public function blockChange(FilesSettings $blockid)
    {
        //dd($blockid);
        $blockid->block = !$blockid->block; //Flip bool value
        $blockid->save();
        return redirect(route('home'));
    }

    public function GetURL(Securefile $fileurl)
    {
        $path = storage_path("app/private/" . $fileurl->file_detail);
        if (!file_exists($path)) {
            abort(404, "File not found.");
        }
        return response()->download($path);
        //return Storage::disk('local')->download($fileurl->file_detail);
        //return response()->download(storage_path('app/private/' . $fileurl->file_detail));
    }
    public function GetThumbnail(Securefile $fileurl)
    {
        $path = storage_path("app/private/" . $fileurl->thumbnail);
        if (!file_exists($path)) {
            abort(404, "File not found.");
        }
        return response()->file($path);
    }
    public function posts()
    {
        return view('posts');
    }
}
