<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\LoginController;
use App\Presentation\Http\Controllers\RefreshController;
use App\Presentation\Http\Controllers\ProxyController;

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

Route::post('/login', LoginController::class);
Route::post('/refresh', RefreshController::class);

Route::middleware('keycloak.jwt')->group(function () {
    Route::get('/users', [ProxyController::class, 'users']);
    Route::get('/posts', [ProxyController::class, 'posts']);
});
