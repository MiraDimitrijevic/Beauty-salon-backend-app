<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;
    protected $fillable = [
        'service_id',
        'user_id'
    ];

    public function appointmentItem(){
        return $this->hasMany(AppointmentItem::class);
    }

    public function service(){
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }
}
