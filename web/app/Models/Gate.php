<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'location',
        'description',
        'gate_id',
        'is_active',
        'door_id',
        'door_ip_address',
        'integration_status',
        'last_heartbeat_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_heartbeat_at' => 'datetime',
        ];
    }

    // ==================== Relationships ====================

    /**
     * Get tasks that include this gate.
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class)->withTimestamps();
    }

    /**
     * Get access logs for this gate.
     */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(DoorAccessLog::class);
    }

    // ==================== Scopes ====================

    /**
     * Scope to only active gates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to only integrated gates.
     */
    public function scopeIntegrated($query)
    {
        return $query->where('integration_status', 'integrated');
    }

    // ==================== Helper Methods ====================

    /**
     * Check if this gate is integrated with a physical door.
     */
    public function isIntegrated(): bool
    {
        return $this->integration_status === 'integrated' && $this->door_id !== null;
    }

    /**
     * Check if the gate's IoT device is online (heartbeat within last 5 minutes).
     */
    public function isOnline(): bool
    {
        if (!$this->last_heartbeat_at) {
            return false;
        }
        return $this->last_heartbeat_at->diffInMinutes(now()) < 5;
    }

    /**
     * Get the integration status label for display.
     * Shows relative heartbeat time when online.
     */
    public function getIntegrationStatusLabelAttribute(): string
    {
        if ($this->integration_status === 'integrated') {
            if ($this->isOnline() && $this->last_heartbeat_at) {
                return $this->last_heartbeat_at->diffForHumans();
            }
            return 'Offline';
        }
        
        return 'Not Integrated';
    }

    /**
     * Get the last heartbeat as a relative time string.
     */
    public function getLastHeartbeatRelativeAttribute(): ?string
    {
        if (!$this->last_heartbeat_at) {
            return null;
        }
        return $this->last_heartbeat_at->diffForHumans();
    }
}
