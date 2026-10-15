<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SaveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($task = $this->route('task')) {
            Gate::authorize('update', $task);
        }

        return $this->user() !== null;
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
            'is_completed' => ['sometimes', 'boolean'],
            'user_id' => $this->user()->is_admin
                ? ['sometimes', 'required', 'integer', Rule::exists('users', 'id')]
                : ['prohibited'],
        ];
    }
}
