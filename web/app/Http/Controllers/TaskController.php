<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Gate;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;

    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of tasks.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'from_date', 'to_date']);
        $tasks = $this->taskService->getTasksForUser($request->user(), $filters);

        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        $this->authorize('create', Task::class);

        $vendors = User::where('role', 'vendor')->get();
        $pics = User::whereIn('role', ['dcfm', 'soc'])->get();
        $gates = Gate::active()->get();

        return view('tasks.create', compact('vendors', 'pics', 'gates'));
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'vendor_ids' => 'required|array|min:1',
            'vendor_ids.*' => 'exists:users,id',
            'pic_id' => 'required|exists:users,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'gate_ids' => 'required|array|min:1',
            'gate_ids.*' => 'exists:gates,id',
            'title' => 'required|string|max:255',
        ]);

        $result = $this->taskService->createTask($validated, $request->user());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error']])->withInput();
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load(['vendors', 'pic', 'gates', 'creator']);

        return view('tasks.show', compact('task'));
    }

    /**
     * Revoke the specified task.
     */
    public function revoke(Request $request, Task $task)
    {
        $this->authorize('revoke', $task);

        $result = $this->taskService->revokeTask(
            $task,
            $request->user(),
            $request->input('reason')
        );

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error']]);
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Task revoked successfully.');
    }

    /**
     * Complete the specified task.
     */
    public function complete(Task $task)
    {
        $this->authorize('complete', $task);

        $result = $this->taskService->completeTask($task);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error']]);
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Task completed successfully.');
    }
}
