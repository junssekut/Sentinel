<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'user_id',
        'details',
        'ip_address',
        'success',
        'reason',
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
            'success' => 'boolean',
        ];
    }

    // ==================== Relationships ====================

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== Scopes ====================

    /**
     * Scope to successful actions.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope to failed actions.
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope to a specific action type.
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to a specific entity.
     */
    public function scopeForEntity($query, string $entityType, ?int $entityId = null)
    {
        $query->where('entity_type', $entityType);
        
        if ($entityId !== null) {
            $query->where('entity_id', $entityId);
        }

        return $query;
    }

    // ==================== Static Factory Methods ====================

    /**
     * Log a task creation.
     */
    public static function logTaskCreated(Task $task, ?int $userId = null, ?string $ipAddress = null): self
    {
        return static::create([
            'action' => 'task_created',
            'entity_type' => 'task',
            'entity_id' => $task->id,
            'user_id' => $userId,
            'details' => [
                'vendor_id' => $task->vendor_id,
                'pic_id' => $task->pic_id,
                'start_time' => $task->start_time->toIso8601String(),
                'end_time' => $task->end_time->toIso8601String(),
            ],
            'ip_address' => $ipAddress,
            'success' => true,
        ]);
    }

    /**
     * Log a task revocation.
     */
    public static function logTaskRevoked(Task $task, ?int $userId = null, ?string $ipAddress = null, ?string $reason = null): self
    {
        return static::create([
            'action' => 'task_revoked',
            'entity_type' => 'task',
            'entity_id' => $task->id,
            'user_id' => $userId,
            'details' => [
                'vendor_id' => $task->vendor_id,
                'pic_id' => $task->pic_id,
            ],
            'ip_address' => $ipAddress,
            'success' => true,
            'reason' => $reason,
        ]);
    }

    /**
     * Log an access validation attempt.
     */
    public static function logAccessValidation(
        int $vendorId,
        int $picId,
        string $gateId,
        bool $approved,
        ?string $reason = null,
        ?array $details = null,
        ?string $ipAddress = null
    ): self {
        return static::create([
            'action' => 'access_validated',
            'entity_type' => 'access_request',
            'entity_id' => null,
            'user_id' => null,
            'details' => array_merge([
                'vendor_id' => $vendorId,
                'pic_id' => $picId,
                'gate_id' => $gateId,
            ], $details ?? []),
            'ip_address' => $ipAddress,
            'success' => $approved,
            'reason' => $reason,
        ]);
    }
}
