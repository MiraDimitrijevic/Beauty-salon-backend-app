<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact'
    ];

    public function appointment(){
        return $this->hasMany(Appointment::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
