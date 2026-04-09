<?php

use App\Http\Controllers\Api\ActivityFeedController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\ValidateApiContentType::class, 'throttle:60,1'])
    ->get('/activities', ActivityFeedController::class);
