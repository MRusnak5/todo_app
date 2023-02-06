<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Wildside\Userstamps;

class TodoList extends Model
{
    use HasFactory,SoftDeletes,Userstamps\Userstamps;

    protected $casts =
        [
            'finished' => 'boolean'
        ];

    protected $fillable =
        [
            'name',
            'description',
            'finished',
            'task_categories_id'

        ];

    public function category()
    {
        return $this->belongsTo(TaskCategories::class);
    }

    public function users()
    {

        return $this->belongsToMany(User::class, 'todoLists_users', 'todo_lists_id', 'user_id');
    }
}
