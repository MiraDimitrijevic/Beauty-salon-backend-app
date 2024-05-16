<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'profession',
        'file_name',
        'image_data',
        'mime_type',
        'user_id'
    ];

    protected $primary_key = 'user_id';

    public function offer(){
        return $this->hasMany(Offer::class);
    }

    public function schedule(){
        return $this->hasMany(Schedule::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

}
