<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Gate;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get stats based on user role
        $stats = $this->getStatsForUser($user);
        
        // Get recent tasks
        $recentTasks = $this->getRecentTasksForUser($user);

        // Get recent access logs (for DCFM and SOC)
        $recentLogs = collect();
        if ($user->canViewAllTasks()) {
            $recentLogs = AuditLog::forAction('access_validated')
                ->latest()
                ->take(10)
                ->get();
        }

        return view('dashboard', compact('user', 'stats', 'recentTasks', 'recentLogs'));
    }

    /**
     * Get dashboard statistics for user.
     */
    private function getStatsForUser(User $user): array
    {
        $stats = [];

        if ($user->isVendor()) {
            $stats['active_tasks'] = Task::active()->where('vendor_id', $user->id)->count();
            $stats['completed_tasks'] = Task::completed()->where('vendor_id', $user->id)->count();
        } else {
            // DCFM and SOC see all stats
            $stats['active_tasks'] = Task::active()->count();
            $stats['total_vendors'] = User::where('role', 'vendor')->count();
            $stats['total_gates'] = Gate::active()->count();
            $stats['today_access_attempts'] = AuditLog::forAction('access_validated')
                ->whereDate('created_at', today())
                ->count();
            $stats['today_denied'] = AuditLog::forAction('access_validated')
                ->failed()
                ->whereDate('created_at', today())
                ->count();
        }

        return $stats;
    }

    /**
     * Get recent tasks for user.
     */
    private function getRecentTasksForUser(User $user)
    {
        $query = Task::with(['vendor', 'pic', 'gates']);

        if ($user->isVendor()) {
            $query->where('vendor_id', $user->id);
        }

        return $query->latest()->take(5)->get();
    }
}
