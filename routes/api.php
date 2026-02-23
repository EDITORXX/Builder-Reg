<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuilderController;
use App\Http\Controllers\Api\CpApplicationController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\LockController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\VisitController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/cp/register', [AuthController::class, 'registerCp']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::middleware('role:super_admin')->group(function () {
        Route::post('/builders', [BuilderController::class, 'store']);
        Route::get('/builders/{builder}', [BuilderController::class, 'show']);
    });

    Route::middleware('role:super_admin,builder_admin')->group(function () {
        Route::post('/builders/{builder}/projects', [ProjectController::class, 'store']);
        Route::patch('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
    });

    Route::get('/projects', [ProjectController::class, 'index']);

    Route::middleware('role:channel_partner')->group(function () {
        Route::post('/cp/apply', [CpApplicationController::class, 'apply']);
        Route::get('/cp/my-applications', [CpApplicationController::class, 'myApplications']);
    });

    Route::middleware('role:super_admin,builder_admin,manager')->group(function () {
        Route::get('/cp-applications', [CpApplicationController::class, 'index']);
        Route::post('/cp-applications/{cpApplication}/approve', [CpApplicationController::class, 'approve']);
        Route::post('/cp-applications/{cpApplication}/reject', [CpApplicationController::class, 'reject']);
        Route::post('/cp-applications/{cpApplication}/needs-info', [CpApplicationController::class, 'needsInfo']);
    });

    Route::get('/locks/check', [LockController::class, 'check']);
    Route::get('/locks', [LockController::class, 'index']);
    Route::post('/locks/{lock}/force-unlock', [LockController::class, 'forceUnlock']);

    Route::get('/leads', [LeadController::class, 'index']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::get('/leads/{lead}', [LeadController::class, 'show']);
    Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus']);
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assign']);

    Route::post('/leads/{lead}/visits', [VisitController::class, 'store']);
    Route::patch('/visits/{visit}/reschedule', [VisitController::class, 'reschedule']);
    Route::post('/visits/{visit}/cancel', [VisitController::class, 'cancel']);
    Route::post('/visits/{visit}/otp/send', [VisitController::class, 'sendOtp']);
    Route::post('/visits/{visit}/otp/verify', [VisitController::class, 'verifyOtp']);
    Route::post('/visits/{visit}/confirm', [VisitController::class, 'confirm']);

    Route::middleware('role:super_admin,builder_admin,manager,viewer')->group(function () {
        Route::get('/reports/leads', [ReportController::class, 'leads']);
        Route::get('/reports/locks', [ReportController::class, 'locks']);
        Route::get('/reports/cp-performance', [ReportController::class, 'cpPerformance']);
        Route::get('/reports/conversion', [ReportController::class, 'conversion']);
    });
});
