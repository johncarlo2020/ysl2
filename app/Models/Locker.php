<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locker extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sku_code', 'description', 'tier', 'percentage', 'allocation', 'available'];
}
