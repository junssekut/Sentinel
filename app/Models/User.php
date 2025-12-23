<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'face_image',
        'face_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'face_image', // Hide face image from serialization by default
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==================== Role Helper Methods ====================

    /**
     * Check if user is a Vendor.
     */
    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    /**
     * Check if user is a DCFM (Data Center Facility Manager).
     */
    public function isDcfm(): bool
    {
        return $this->role === 'dcfm';
    }

    /**
     * Check if user is SOC (Security Operation Center).
     */
    public function isSoc(): bool
    {
        return $this->role === 'soc';
    }

    /**
     * Check if user can manage tasks (DCFM only).
     */
    public function canManageTasks(): bool
    {
        return $this->isDcfm();
    }

    /**
     * Check if user can view all tasks (DCFM and SOC).
     */
    public function canViewAllTasks(): bool
    {
        return $this->isDcfm() || $this->isSoc();
    }

    // ==================== Relationships ====================

    /**
     * Get tasks where this user is the vendor.
     */
    public function vendorTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'vendor_id');
    }

    /**
     * Get tasks where this user is the PIC (Person in Charge).
     */
    public function picTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'pic_id');
    }

    /**
     * Get tasks created by this user.
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Get audit logs for this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
