<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'appointment_id',
        'start_time',
        'end_time'
    ];



    public function offer(){
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function appointment(){
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}
