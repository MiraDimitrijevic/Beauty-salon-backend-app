<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableTime extends Model
{
    use HasFactory;

    use HasFactory;
    protected $fillable = [
        'date',
        'time',
        'user_id'
    ];

    public function employee(){
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }

    public function schedule(){
        return $this->belongsTo(Schedule::class, 'date', 'date');
    }

}
