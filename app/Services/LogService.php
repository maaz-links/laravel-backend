<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
class LogService implements LogServiceInt
{
    public function sendMessage($to)
    {
        $showtime = "Sending messsage to $to with Service.\n";
        Log::info($showtime);
    }
}
