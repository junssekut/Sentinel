<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\Gate;
use App\Models\AuditLog;
use Carbon\Carbon;

class AccessValidationService
{
    /**
     * Validation result structure.
     */
    private bool $approved = false;
    private string $reason = '';
    private ?User $vendor = null;
    private ?User $pic = null;
    private ?Task $task = null;
    private ?Gate $gate = null;

    /**
     * Validate access request from IoT device.
     *
     * @param int $vendorId The ID of the vendor user
     * @param int $picId The ID of the PIC user
     * @param string $gateId The gate identifier
     * @param string|null $timestamp ISO-8601 timestamp (optional)
     * @return array{approved: bool, reason: string}
     */
    public function validate(
        int $vendorId,
        int $picId,
        string $gateId,
        ?string $timestamp = null,
        ?string $ipAddress = null
    ): array {
        // Reset state
        $this->reset();

        // Step 1: Verify vendor exists
        $this->vendor = User::find($vendorId);
        if (!$this->vendor) {
            return $this->deny('Vendor not found', $vendorId, $picId, $gateId, $ipAddress);
        }

        // Step 2: Verify vendor is actually a vendor
        if (!$this->vendor->isVendor()) {
            return $this->deny('Invalid vendor role', $vendorId, $picId, $gateId, $ipAddress);
        }

        // Step 3: Verify PIC exists
        $this->pic = User::find($picId);
        if (!$this->pic) {
            return $this->deny('PIC not found', $vendorId, $picId, $gateId, $ipAddress);
        }

        // Step 4: Verify PIC is not a vendor (must be DCFM, SOC, or PIC)
        if ($this->pic->isVendor()) {
            return $this->deny('Invalid PIC role', $vendorId, $picId, $gateId, $ipAddress);
        }

        // Step 5: Verify gate exists
        $this->gate = Gate::where('gate_id', $gateId)->first();
        if (!$this->gate) {
            return $this->deny('Gate not found', $vendorId, $picId, $gateId, $ipAddress);
        }

        // Step 6: Verify gate is active
        if (!$this->gate->is_active) {
            return $this->deny('Gate is inactive', $vendorId, $picId, $gateId, $ipAddress);
        }

        // Step 7: Find active task with vendor and PIC assigned together
        $this->task = Task::active()
            ->forVendor($this->vendor->id)
            ->where('pic_id', $this->pic->id)
            ->first();

        if (!$this->task) {
            return $this->deny('No active task found for this vendor-PIC pair', $vendorId, $picId, $gateId, $ipAddress);
        }

        // Step 8: Verify task is within time window
        if (!$this->task->isCurrentlyValid()) {
            return $this->deny('Task is outside valid time window', $vendorId, $picId, $gateId, $ipAddress);
        }

        // Step 9: Verify gate is allowed for this task
        if (!$this->task->allowsGate($gateId)) {
            return $this->deny('Gate not authorized for this task', $vendorId, $picId, $gateId, $ipAddress);
        }

        // All checks passed - approve access
        return $this->approve($vendorId, $picId, $gateId, $ipAddress);
    }

    /**
     * Reset validation state.
     */
    private function reset(): void
    {
        $this->approved = false;
        $this->reason = '';
        $this->vendor = null;
        $this->pic = null;
        $this->task = null;
        $this->gate = null;
    }

    /**
     * Deny access and log the attempt.
     */
    private function deny(string $reason, int $vendorId, int $picId, string $gateId, ?string $ipAddress): array
    {
        $this->approved = false;
        $this->reason = $reason;

        // Log the denial
        AuditLog::logAccessValidation(
            $vendorId,
            $picId,
            $gateId,
            false,
            $reason,
            [
                'vendor_id' => $this->vendor?->id,
                'pic_id' => $this->pic?->id,
                'task_id' => $this->task?->id,
                'gate_record_id' => $this->gate?->id,
            ],
            $ipAddress
        );

        return [
            'approved' => false,
            'reason' => $reason,
        ];
    }

    /**
     * Approve access and log the attempt.
     */
    private function approve(int $vendorId, int $picId, string $gateId, ?string $ipAddress): array
    {
        $this->approved = true;
        $this->reason = 'OK';

        // Log the approval
        AuditLog::logAccessValidation(
            $vendorId,
            $picId,
            $gateId,
            true,
            'OK',
            [
                'vendor_id' => $this->vendor->id,
                'pic_id' => $this->pic->id,
                'task_id' => $this->task->id,
                'gate_record_id' => $this->gate->id,
            ],
            $ipAddress
        );

        return [
            'approved' => true,
            'reason' => 'OK',
        ];
    }
}
