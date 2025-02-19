<?php

namespace App\Http\Controllers;

use App\Models\BackendConfig;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function index()
    {
        $configs = BackendConfig::orderBy('key')->get();
        return view('configs.index', compact('configs'));
    }

    public function create()
    {
        return view('configs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:configs,key',
            'value' => 'required|string',
        ]);

        BackendConfig::create($request->only(['key', 'value']));

        return redirect()->route('configs.index')->with('success', 'Configuration created successfully.');
    }

    public function edit(BackendConfig $mail_config)
    {
        return view('configs.edit', compact('mail_config'));
    }

    public function update(Request $request, BackendConfig $mail_config)
    {
        $request->validate([
            'key' => 'required|string|unique:configs,key,' . $mail_config->id,
            'value' => 'required|string',
        ]);

        $mail_config->update($request->only(['key', 'value']));

        return redirect()->route('configs.index')->with('success', 'Configuration updated successfully.');
    }

    public function destroy(BackendConfig $mail_config)
    {
        $mail_config->delete();

        return redirect()->route('configs.index')->with('success', 'Configuration deleted successfully.');
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