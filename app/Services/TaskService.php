<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;

class TaskService
{
    /**
     * Create a new task.
     */
    public function createTask(User $user, array $data): Task
    {
        return Task::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? now(),
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'pending',
        ]);
    }
}
