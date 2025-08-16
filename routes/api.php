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
use App\Http\Controllers\API\LocationsController;
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
        Route::get('/users/form-options', [UserController::class, 'formOptions']); // Get roles, statuses, locations for dropdowns
        Route::post('/users', [UserController::class, 'store']); // Create new user
        Route::put('/users/{id}', [UserController::class, 'update']);   // Update user
        Route::delete('/users/{id}', [UserController::class, 'destroy']); // Soft delete user

         // Clients
        Route::get('/clients', [ClientController::class, 'index']); // List clients (with search & pagination)
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
 
         // ✅ Statuses (CRUD)
         Route::get('/statuses', [StatusController::class, 'index']);
         Route::post('/statuses', [StatusController::class, 'store']);
         Route::get('/statuses/{id}', [StatusController::class, 'show']);
         Route::put('/statuses/{id}', [StatusController::class, 'update']);
         Route::delete('/statuses/{id}', [StatusController::class, 'destroy']);

        // Locations
        Route::get('/locations', [LocationsController::class, 'index']);
        Route::post('/locations', [LocationsController::class, 'store']);
        Route::get('/locations/{location}', [LocationsController::class, 'show']);
        Route::put('/locations/{location}', [LocationsController::class, 'update']);
        Route::delete('/locations/{location}', [LocationsController::class, 'destroy']);


    });

    /**
     * Preflight OPTIONS catch-all for CORS
     */
    Route::options('/{any}', function () {
        return response()->json(['message' => 'Preflight OK'], 204);
    })->where('any', '.*');
});