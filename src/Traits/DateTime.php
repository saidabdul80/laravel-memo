<?php
namespace Saidabdulsalam\LaravelMemo\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait DateTime
{
   
    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: function($val){
                $created_at = Carbon::parse($val);
                return $created_at->format("Y-m-d H:i");
            },
            set: fn (string $value) =>$value,
        );
    }

    public function getTimeAtAttribute()
    {        
        $created_at = Carbon::parse($this->updated_at);
        $current_time = Carbon::now();
    
        // Calculate the time difference between now and the 'created_at' timestamp
        $diff_seconds = floor($created_at->diffInSeconds($current_time));
        $diff_minutes = floor($created_at->diffInMinutes($current_time));
        $diff_hours = floor($created_at->diffInHours($current_time));
        $diff_days = floor($created_at->diffInDays($current_time));
        $diff_weeks = floor($created_at->diffInWeeks($current_time));
        $diff_months = floor($created_at->diffInMonths($current_time));
        $diff_years = floor($created_at->diffInYears($current_time));

        // Format the output based on the time difference
        if ($diff_seconds < 60) {
            $time_ago = "$diff_seconds second" . ($diff_seconds > 1 ? 's' : '') . " ago";
        } elseif ($diff_minutes < 60) {
            $time_ago = "$diff_minutes minute" . ($diff_minutes > 1 ? 's' : '') . " ago";
        } elseif ($diff_hours < 24) {
            $time_ago = "$diff_hours hour" . ($diff_hours > 1 ? 's' : '') . " ago";
        } elseif ($diff_days < 7) {
            $time_ago = "$diff_days day" . ($diff_days > 1 ? 's' : '') . " ago";
        }else{
            $time_ago = $created_at->format("d F, Y, H:i A");
        }
        //elseif ($diff_weeks < 4) {
        //     $time_ago = "$diff_weeks week" . ($diff_weeks > 1 ? 's' : '') . " ago";
        // } elseif ($diff_months < 12) {
        //     $time_ago = "$diff_months month" . ($diff_months > 1 ? 's' : '') . " ago";
        // } else {
        //     $time_ago = "$diff_years year" . ($diff_years > 1 ? 's' : '') . " ago";
        // }
    
        return $time_ago;   
    }
}
