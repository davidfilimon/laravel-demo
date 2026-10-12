<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            ['title' => 'Plan the week ahead', 'description' => 'Choose three meaningful goals and break them into manageable steps.', 'priority' => 'high', 'is_completed' => false],
            ['title' => 'Review the dashboard design', 'description' => 'Check the layout, typography, and mobile experience.', 'priority' => 'high', 'is_completed' => false],
            ['title' => 'Organize project notes', 'description' => 'Bring ideas, decisions, and useful links together in one place.', 'priority' => 'medium', 'is_completed' => false],
            ['title' => 'Clear the inbox', 'description' => 'Reply to outstanding messages and archive the rest.', 'priority' => 'low', 'is_completed' => false],
            ['title' => 'Set up the workspace', 'description' => 'Make a little room for focused work.', 'priority' => 'medium', 'is_completed' => true],
            ['title' => 'Write the project brief', 'description' => 'Define the problem, the audience, and what success looks like.', 'priority' => 'high', 'is_completed' => true],
        ];

        foreach ($tasks as $task) {
            Task::firstOrCreate(['title' => $task['title']], $task);
        }
    }
}
