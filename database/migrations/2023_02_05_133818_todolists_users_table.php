<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todoLists_users', function (Blueprint $table) {
            $table->foreignId('todo_lists_id')->constrained('todo_lists');
            $table->foreignId('user_id')->constrained('users');

        });
    }

    public function down()
    {
        Schema::dropIfExists('role_user');
    }
};
