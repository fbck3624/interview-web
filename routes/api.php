<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyController;
use App\Http\Middleware\CheckCommentPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::put('verify/{id}', [AuthController::class, 'verify']);
Route::post('send-email/{id}', [AuthController::class, 'sendEmail']);
Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');
Route::prefix('company')->group(function () {
    Route::get('/', [CompanyController::class, 'index']);
    Route::get('{id}', [CompanyController::class, 'show']);
});
Route::prefix('comment')->group(function () {
    Route::get('{id}', [CommentController::class, 'show']);
    Route::post('/', [CommentController::class, 'store']);
    Route::put('{id}', [CommentController::class, 'update']);
    Route::delete('{id}', [CommentController::class, 'destory']);
});
