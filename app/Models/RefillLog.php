<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefillLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'locker_id',
        'type',
        'previous_amount',
        'quantity_added',
        'new_amount',
        'user_id',
        'notes'
    ];

    public function locker()
    {
        return $this->belongsTo(Locker::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get total quantity added for a specific locker
     */
    public static function getTotalAddedByLocker($lockerId)
    {
        return self::where('locker_id', $lockerId)
            ->where('type', 'refill')
            ->sum('quantity_added');
    }

    /**
     * Get total quantity added for all lockers
     */
    public static function getTotalAddedPerLocker()
    {
        return self::selectRaw('locker_id, SUM(quantity_added) as total_added')
            ->where('type', 'refill')
            ->groupBy('locker_id')
            ->pluck('total_added', 'locker_id');
    }
}
