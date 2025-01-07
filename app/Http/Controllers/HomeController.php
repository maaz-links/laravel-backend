<?php

namespace App\Http\Controllers;

use App\Models\FilesSettings;
use App\Models\FilesSettingsView;
use App\Models\Securefile;
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
        $settings = FilesSettingsView::all();
        return view('home', ['settings' => $settings]);
    }

    public function blockChange(FilesSettings $blockid)
    {
        //dd($blockid);
        $blockid->block = !$blockid->block; //Flip bool value
        $blockid->save();
        return redirect(route('home'));
    }

    public function GetURL(Securefile $fileurl){
        return Storage::disk('public')->download($fileurl->file_detail);
    }
    public function posts()
    {
        return view('posts');
    }
}
