<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\TodoListController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {

        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);


    });

    Route::middleware(['auth:api'])->group(function () {
        Route::patch('todoLists/{todoList}/restore',[TodoListController::class,'restore']);
        Route::put('todoLists/{todoList}/finished',[TodoListController::class,'finished']);
        Route::post('todoLists/{todoList}/shareTask',[TodoListController::class,'shareTask']);
        Route::get('todoLists/indexSharedTasks',[TodoListController::class,'indexSharedTasks']);
        Route::apiResource('todoLists', TodoListController::class);

    });
});
