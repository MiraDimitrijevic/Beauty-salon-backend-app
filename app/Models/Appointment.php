<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'cost',
        'canceled',
        'cancellation_reason'
    ];

    public function client(){
        return $this->belongsTo(Client::class, 'user_id', 'user_id');
    }
    public function appointmentItem(){
        return $this->hasMany(AppointmentItem::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
