<?php

namespace App\Http\Controllers;

use App\Mail\pastelinkmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class EmailController extends Controller
{
    public function sendEmail(Request $request){
        $validator = Validator::make($request->all(), [
            'recipient' => 'required|string',
            'pastelink' => 'required|string',
            'subject' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validation Error",
                'errors' => $validator->errors()->all(),
            ], 401);
        }
        $toEmail = $request->recipient;
        $subject = $request->subject ? $request->subject : "Link for encrypted data";
        $message = "Your Pastelink is ".$request->pastelink;
        
        Mail::to($toEmail)->send(new pastelinkmail($message,$subject));

    }
}
