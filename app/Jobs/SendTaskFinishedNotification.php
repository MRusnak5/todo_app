<?php

namespace App\Jobs;

use App\Models\TodoList;
use App\Notifications\TaskFinishedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendTaskFinishedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        $todoList = TodoList::findOrFail($this->id);
        $users = $todoList->users;
        $users = $users->push($todoList->creator);

        $data = [
            'subject' => 'Task Finished',
            'title' => 'Task Finished',
            'body' => 'Your task have been marked as finished! Task name: ' . $todoList->name . " Task description: " . $todoList->description,
            'thankyou' => 'Thank you'
        ];
        Notification::send($users, new TaskFinishedNotification($data));

    }


}
