<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'pic_id',
        'start_time',
        'end_time',
        'status',
        'notes',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    // ==================== Relationships ====================

    /**
     * Get the vendor for this task.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the PIC (Person in Charge) for this task.
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    /**
     * Get the user who created this task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the gates allowed for this task.
     */
    public function gates(): BelongsToMany
    {
        return $this->belongsToMany(Gate::class)->withTimestamps();
    }

    // ==================== Scopes ====================

    /**
     * Scope to only active tasks.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to only completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to only revoked tasks.
     */
    public function scopeRevoked($query)
    {
        return $query->where('status', 'revoked');
    }

    /**
     * Scope to tasks currently within their time window.
     */
    public function scopeWithinTimeWindow($query)
    {
        $now = Carbon::now();
        return $query->where('start_time', '<=', $now)
                     ->where('end_time', '>=', $now);
    }

    /**
     * Scope to tasks for a specific vendor.
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope to tasks for a specific PIC.
     */
    public function scopeForPic($query, $picId)
    {
        return $query->where('pic_id', $picId);
    }

    // ==================== Helper Methods ====================

    /**
     * Check if the task is currently active and within its time window.
     */
    public function isCurrentlyValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = Carbon::now();
        return $now->between($this->start_time, $this->end_time);
    }

    /**
     * Check if a specific gate is allowed for this task.
     */
    public function allowsGate(string $gateId): bool
    {
        return $this->gates()->where('gate_id', $gateId)->exists();
    }

    /**
     * Mark the task as completed.
     */
    public function markAsCompleted(): bool
    {
        $this->status = 'completed';
        return $this->save();
    }

    /**
     * Mark the task as revoked.
     */
    public function markAsRevoked(): bool
    {
        $this->status = 'revoked';
        return $this->save();
    }
}
