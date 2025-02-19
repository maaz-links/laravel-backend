<?php

namespace App\Http\Controllers;

use App\Mail\pastelinkmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class EmailController extends Controller
{
    public function sendEmail(Request $request)
    {
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
        $emails = array_map('trim', explode(',', $request->recipient));

        // Filter out invalid emails
        $validEmails = array_filter($emails, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        });

        // Reindex the array (optional)
        $validEmails = array_values($validEmails);
        if (empty($validEmails)) {
            return response()->json([
                'status' => false,
                'message' => "No valid email",
            ], 401);
        }
        $toEmail = array_shift($validEmails); // First email as 'to'
        $ccEmails = $validEmails; // Remaining emails as CC

        //$toEmail = $request->recipient;
        $subject = config('mail.customize.subject') ?: "Link for encrypted data";
        $message = config('mail.customize.body') ?: "Your Pastelink is {link}";
        if(true){
            $message = str_replace("{link}", $request->pastelink, $message);
        }
        //dd($subject,$message);
        Mail::to($toEmail)->cc($ccEmails)->send(new pastelinkmail($message, $subject));

    }
}
