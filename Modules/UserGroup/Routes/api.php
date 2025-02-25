<?php

use Illuminate\Support\Facades\Route;
use Modules\UserGroup\Http\Controllers\UserGroupController;

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
Route::middleware(['auth:api'])->prefix('admin')->group(
    function () {
        Route::apiResource('user-group', UserGroupController::class);
    }
);