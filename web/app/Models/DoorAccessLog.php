<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoorAccessLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'gate_id',
        'task_id',
        'vendor_id',
        'pic_id',
        'event_type',
        'reason',
        'details',
        'session_id',
        'client_ip',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    // ==================== Relationships ====================

    /**
     * Get the gate for this access log.
     */
    public function gate(): BelongsTo
    {
        return $this->belongsTo(Gate::class);
    }

    /**
     * Get the task for this access log.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the vendor for this access log.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the PIC for this access log.
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    // ==================== Scopes ====================

    /**
     * Scope to entry events.
     */
    public function scopeEntries($query)
    {
        return $query->where('event_type', 'entry');
    }

    /**
     * Scope to exit events.
     */
    public function scopeExits($query)
    {
        return $query->where('event_type', 'exit');
    }

    /**
     * Scope to denied events.
     */
    public function scopeDenied($query)
    {
        return $query->where('event_type', 'denied');
    }

    /**
     * Scope to a specific gate.
     */
    public function scopeForGate($query, $gateId)
    {
        return $query->where('gate_id', $gateId);
    }

    /**
     * Scope to recent logs.
     */
    public function scopeRecent($query, $limit = 20)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // ==================== Static Factory Methods ====================

    /**
     * Log a door entry event.
     */
    public static function logEntry(
        int $gateId,
        ?int $taskId = null,
        ?int $vendorId = null,
        ?int $picId = null,
        ?string $sessionId = null,
        ?string $clientIp = null,
        ?array $details = null
    ): self {
        return static::create([
            'gate_id' => $gateId,
            'task_id' => $taskId,
            'vendor_id' => $vendorId,
            'pic_id' => $picId,
            'event_type' => 'entry',
            'session_id' => $sessionId,
            'client_ip' => $clientIp,
            'details' => $details,
        ]);
    }

    /**
     * Log a door exit event.
     */
    public static function logExit(
        int $gateId,
        ?int $taskId = null,
        ?int $vendorId = null,
        ?int $picId = null,
        ?string $sessionId = null,
        ?string $clientIp = null
    ): self {
        return static::create([
            'gate_id' => $gateId,
            'task_id' => $taskId,
            'vendor_id' => $vendorId,
            'pic_id' => $picId,
            'event_type' => 'exit',
            'session_id' => $sessionId,
            'client_ip' => $clientIp,
        ]);
    }

    /**
     * Log a denied access event.
     */
    public static function logDenied(
        int $gateId,
        string $reason,
        ?int $vendorId = null,
        ?int $picId = null,
        ?string $sessionId = null,
        ?string $clientIp = null,
        ?array $details = null
    ): self {
        return static::create([
            'gate_id' => $gateId,
            'vendor_id' => $vendorId,
            'pic_id' => $picId,
            'event_type' => 'denied',
            'reason' => $reason,
            'session_id' => $sessionId,
            'client_ip' => $clientIp,
            'details' => $details,
        ]);
    }
}
