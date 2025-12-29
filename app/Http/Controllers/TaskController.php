<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display all tasks.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $status = $request->get('status');
        $priority = $request->get('priority');

        $query = $user->tasks();

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by priority
        if ($priority) {
            $query->where('priority', $priority);
        }

        // Order by priority and due date
        $tasks = $query->orderByRaw("
            CASE priority
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
            END
        ")
        ->orderBy('due_date', 'asc')
        ->paginate(20);

        // Get counts for filters
        $counts = [
            'all' => $user->tasks()->count(),
            'pending' => $user->tasks()->pending()->count(),
            'in_progress' => $user->tasks()->inProgress()->count(),
            'completed' => $user->tasks()->completed()->count(),
            'overdue' => $user->tasks()->overdue()->count(),
        ];

        return view('tasks.index', compact('tasks', 'counts', 'status', 'priority'));
    }

    /**
     * Show form to create new task.
     */
    public function create()
    {
        return view('tasks.create');
    }

    /**
     * Store new task.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'due_date' => 'nullable|date',
            'reminder_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Task::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => $request->status,
            'due_date' => $request->due_date,
            'reminder_at' => $request->reminder_at,
            'tags' => $request->tags,
        ]);

        return redirect()->route('tasks.index')
            ->with('success', 'Task berhasil dibuat!');
    }

    /**
     * Show task details.
     */
    public function show(Task $task)
    {
        // Ensure user owns this task
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        return view('tasks.show', compact('task'));
    }

    /**
     * Show form to edit task.
     */
    public function edit(Task $task)
    {
        // Ensure user owns this task
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        return view('tasks.edit', compact('task'));
    }

    /**
     * Update task.
     */
    public function update(Request $request, Task $task)
    {
        // Ensure user owns this task
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'due_date' => 'nullable|date',
            'reminder_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => $request->status,
            'due_date' => $request->due_date,
            'reminder_at' => $request->reminder_at,
            'tags' => $request->tags,
        ]);

        return back()->with('success', 'Task berhasil diupdate!');
    }

    /**
     * Delete task.
     */
    public function destroy(Task $task)
    {
        // Ensure user owns this task
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task berhasil dihapus!');
    }

    /**
     * Mark task as completed.
     */
    public function complete(Task $task)
    {
        // Ensure user owns this task
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        $task->markAsCompleted();

        return back()->with('success', 'Task ditandai selesai!');
    }

    /**
     * Mark task as in progress.
     */
    public function start(Task $task)
    {
        // Ensure user owns this task
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        $task->markAsInProgress();

        return back()->with('success', 'Task dimulai!');
    }

    /**
     * Get calendar view.
     */
    public function calendar()
    {
        $user = auth()->user();
        
        $tasks = $user->tasks()
            ->whereNotNull('due_date')
            ->where('status', '!=', 'completed')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'start' => $task->due_date->format('Y-m-d'),
                    'backgroundColor' => match($task->priority) {
                        'urgent' => '#ef4444',
                        'high' => '#f59e0b',
                        'medium' => '#3b82f6',
                        'low' => '#10b981',
                    },
                    'url' => route('tasks.show', $task),
                ];
            });

        return view('tasks.calendar', compact('tasks'));
    }
}
