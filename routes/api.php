<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Api\PaymentRequestController;
use App\Http\Controllers\Api\PaymentController;

Route::post('/control-numbers', [PaymentRequestController::class, 'store']);
Route::post('/payments', [PaymentController::class, 'pay']);

