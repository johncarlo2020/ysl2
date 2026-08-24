<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfidLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfid_uid',
        'action',
        'user_id',
        'station_id',
        'ip_address',
        'user_agent',
        'status',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user associated with this log entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the station associated with this log entry.
     */
    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Log an RFID activity.
     */
    public static function logActivity(
        string $rfidUid,
        string $action,
        string $status,
        ?int $userId = null,
        ?int $stationId = null,
        ?array $details = null
    ): self {
        return self::create([
            'rfid_uid' => strtoupper(trim($rfidUid)),
            'action' => $action,
            'user_id' => $userId,
            'station_id' => $stationId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => $status,
            'details' => $details,
        ]);
    }

    /**
     * Check if this UID has been scanned too frequently (rate limiting).
     */
    public static function isRateLimited(string $rfidUid, int $maxAttempts = 5, int $decaySeconds = 60): bool
    {
        $recentAttempts = self::where('rfid_uid', strtoupper(trim($rfidUid)))
            ->where('created_at', '>=', now()->subSeconds($decaySeconds))
            ->count();

        return $recentAttempts >= $maxAttempts;
    }

    /**
     * Get recent suspicious activity for monitoring.
     */
    public static function getSuspiciousActivity(int $minutes = 15)
    {
        // Multiple failed attempts from same UID
        $failedAttempts = self::where('status', 'error')
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->select('rfid_uid', \DB::raw('COUNT(*) as attempt_count'))
            ->groupBy('rfid_uid')
            ->having('attempt_count', '>=', 3)
            ->get();

        // Multiple rapid scans from same IP
        $rapidScans = self::where('created_at', '>=', now()->subMinutes($minutes))
            ->select('ip_address', \DB::raw('COUNT(*) as scan_count'))
            ->groupBy('ip_address')
            ->having('scan_count', '>=', 10)
            ->get();

        return [
            'failed_attempts' => $failedAttempts,
            'rapid_scans' => $rapidScans,
        ];
    }
}
