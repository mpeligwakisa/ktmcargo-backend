<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CargoController;
use App\Http\Controllers\API\PeopleController;
use App\Http\Controllers\API\StatusController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\ReportController;

Route::prefix('v1')->group(function () {

    /**
     * Public Authentication Routes
     */
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    /**
     * Protected Routes (require Sanctum auth)
     */
    Route::middleware('auth:sanctum')->group(function () {
        // Authenticated user details
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'summary']);

        // Users
        Route::get('/users', [UserController::class, 'index']); // List users with role info
        Route::get('/users/form-options', [UserController::class, 'formOptions']); // Get roles, status, location for dropdowns
        Route::post('/users', [UserController::class, 'store']); // Create new user
        Route::put('/users/{id}', [UserController::class, 'update']);   // Update user
        Route::delete('/users/{id}', [UserController::class, 'destroy']); // Soft delete user

         // Clients
        Route::get('/clients', [ClientController::class, 'index']); // List clients (with search & pagination)
        Route::get('/clients/form-options', [ClientController::class, 'formOptions']); // Get roles, status, location for dropdowns
        Route::post('/clients', [ClientController::class, 'store']); //add client
        Route::put('/clients/{id}', [ClientController::class, 'update']); //update client
        Route::delete('/clients/{id}', [ClientController::class, 'destroy']); //delete client

        // Cargo
        Route::get('/cargo', [CargoController::class, 'index']);
        Route::post('/cargo', [CargoController::class, 'store']);

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/cargo', [ReportController::class, 'cargoReport']);
            Route::get('/payments', [ReportController::class, 'paymentReport']);
            Route::get('/clients', [ReportController::class, 'clientReport']);
            Route::get('/summary', [ReportController::class, 'summaryReport']);
        });

         // ✅ People (CRUD)
         Route::get('/people', [PeopleController::class, 'index']);
         Route::post('/people', [PeopleController::class, 'store']);
         Route::get('/people/{id}', [PeopleController::class, 'show']);
         Route::put('/people/{id}', [PeopleController::class, 'update']);
         Route::delete('/people/{id}', [PeopleController::class, 'destroy']);

         // Roles endpoint (optional)
         Route::get('/roles', [UserController::class, 'getRoles']);
 
         // ✅ Status (CRUD)
         Route::get('/status', [StatusController::class, 'index']);
         Route::post('/status', [StatusController::class, 'store']);
         Route::get('/status/{id}', [StatusController::class, 'show']);
         Route::put('/status/{id}', [StatusController::class, 'update']);
         Route::delete('/status/{id}', [StatusController::class, 'destroy']);

        // Location
        Route::get('/locations', [LocationController::class, 'index']);
        Route::post('/locations', [LocationController::class, 'store']);
        Route::get('/locations/{id}', [LocationController::class, 'show']);
        Route::put('/locations/{id}', [LocationController::class, 'update']);
        Route::delete('/locations/{id}', [LocationController::class, 'destroy']);


    });

    /**
     * Preflight OPTIONS catch-all for CORS
     */
    Route::options('/{any}', function () {
        return response()->json(['message' => 'Preflight OK'], 204);
    })->where('any', '.*');
});