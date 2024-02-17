<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'user_id'
    ];

    public function employee(){
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }

    public function timeAvailable(){
        return $this->hasMany(AvailableTime::class);
    }
}
