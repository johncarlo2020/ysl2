<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'station_id'
    ];

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
