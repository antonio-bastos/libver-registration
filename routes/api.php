<?php

use App\Http\Controllers\Api\ActivityFeedController;
use Illuminate\Support\Facades\Route;

Route::get('/activities', ActivityFeedController::class);
