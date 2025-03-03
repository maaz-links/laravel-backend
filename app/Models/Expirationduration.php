<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Expirationduration extends Model
{
    protected $table = 'expirationdurations';
    protected $fillable = ['id', 'title', 'duration'];
    protected function title(): Attribute //productName == product_name
    {
        return new Attribute(
            get: fn() => $this->convertMinutes($this->duration)
        );
    }

    protected function convertMinutes($minutes) {
        $MINUTES_IN_HOUR = 60;
        $MINUTES_IN_DAY = 1440; // 60 * 24
        $MINUTES_IN_MONTH = 43200; // 60 * 24 * 30 (assuming 30 days per month)
    
        $months = floor($minutes / $MINUTES_IN_MONTH);
        $minutes %= $MINUTES_IN_MONTH;
    
        $days = floor($minutes / $MINUTES_IN_DAY);
        $minutes %= $MINUTES_IN_DAY;
    
        $hours = floor($minutes / $MINUTES_IN_HOUR);
        $minutes %= $MINUTES_IN_HOUR;
    
        $result = [];
        if ($months) $result[] = "$months month" . ($months > 1 ? "s" : "");
        if ($days) $result[] = "$days day" . ($days > 1 ? "s" : "");
        if ($hours) $result[] = "$hours hour" . ($hours > 1 ? "s" : "");
        if ($minutes) $result[] = "$minutes minute" . ($minutes > 1 ? "s" : "");
    
        return !empty($result) ? implode(", ", $result) : "0 minutes";
    }

}
