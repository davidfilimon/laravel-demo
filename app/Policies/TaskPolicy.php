<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function view(User $user, Task $task): Response
    {
        return $user->is_admin || $task->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function update(User $user, Task $task): Response
    {
        return $this->view($user, $task);
    }

    public function delete(User $user, Task $task): Response
    {
        return $this->view($user, $task);
    }
}
