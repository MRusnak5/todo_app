<?php

namespace App\Jobs;

use App\Models\TodoList;
use App\Models\User;
use App\Notifications\TaskSharedNotification;
use App\Notifications\TaskUnsharedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendTaskUnsharedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id,$users;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id,$users)
    {
        $this->id = $id;
        $this->users = $users;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $todoList = TodoList::findOrFail($this->id);
        $users = User::whereIn('id',$this->users)->get();
        $data = [
            'subject' => 'Task Unshared',
            'title' => 'Task Unshared',
            'body' => 'Task is no longer shared with you! Task name: ' . $todoList->name . " Task description: " . $todoList->description,
            'thankyou' => 'Thank you'
        ];
        Notification::send($users, new TaskUnsharedNotification($data));
    }



}
