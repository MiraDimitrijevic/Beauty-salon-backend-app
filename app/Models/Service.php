<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cost',
        'duration'
    ];

    public function offer(){
        return $this->hasMany(Offer::class);
    }
}
