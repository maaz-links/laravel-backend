<?php

namespace App\Http\Controllers;

use App\Models\MailConfig;
use Illuminate\Http\Request;

class MailSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $settings = MailConfig::first();
        return view('mail.mail-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        //dd($request);
        $request->validate([
            'mail_mailer' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        $settings = MailConfig::first();
        if (!$settings) {
            //dd($request->all());
            $settings = MailConfig::create($request->all());
            //dd($settings);
            return redirect()->back()->with('success', 'Mail settings updated successfully.');
        }

        $settings->update($request->all());

        return redirect()->back()->with('success', 'Mail settings updated successfully.');
    }
}

// MAIL_MAILER=log
// MAIL_HOST=127.0.0.1
// MAIL_PORT=2525
// MAIL_USERNAME=null
// MAIL_PASSWORD=null
// MAIL_ENCRYPTION=null
// MAIL_FROM_ADDRESS="hello@example.com"
// MAIL_FROM_NAME="${APP_NAME}"
// # MAIL_MAILER=smtp
// # MAIL_HOST=smtp.gmail.com
// # MAIL_PORT=587
// # MAIL_USERNAME=mzit3116@gmail.com
// # MAIL_PASSWORD="akvu gdcu tnhw lzok"
// # MAIL_ENCRYPTION=tls
// # MAIL_FROM_ADDRESS="noreplyfilepad@gmail.com"
// # MAIL_FROM_NAME="FilePad"
