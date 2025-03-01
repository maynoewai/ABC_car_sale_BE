<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CarController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\BidController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TestDriveController;


Route::get('/', function () {
    return response()->json(['message' => 'API Only Mode']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('cars', CarController::class);
    
});

// Public bid viewing
Route::get('/cars/{car}/bids', [BidController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('cars', CarController::class)->except(['index', 'show']);
       // Bid placement
       Route::post('/cars/{car}/bids', [BidController::class, 'store']);
       Route::put('/cars/{car}', [CarController::class, 'update']);
       Route::get('/admin/users', [AdminController::class, 'users']);
       Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser']);
       Route::get('/admin/cars', [AdminController::class, 'allCars']);
       Route::delete('/admin/cars/{car}', [AdminController::class, 'deleteCar']);
       Route::get('/admin/cars/{car}', [AdminController::class, 'showCar']);
       Route::put('/admin/users/{user}', [AdminController::class, 'updateUserRole']);
        Route::get('/user', [UserController::class, 'show']);
        Route::put('/user', [UserController::class, 'update']);
        Route::delete('/user', [UserController::class, 'destroy']);
        Route::apiResource('test-drives', TestDriveController::class)->except(['update']);

        // Admin-only routes
        Route::prefix('admin')->group(function () {
            Route::put('/test-drives/{test_drive}', [TestDriveController::class, 'update']);
            Route::get('/test-drives', [TestDriveController::class, 'index']);
            Route::delete('/test-drives/{test_drive}', [TestDriveController::class, 'destroy']);
        });

        Route::get('/admin/bids/', [AdminController::class, 'allBids']);
        Route::put('/admin/bids/{bid}', [AdminController::class, 'updateBid']);
        Route::delete('/admin/bids/{bid}', [AdminController::class, 'deleteBid']);
        Route::get('/user/bids', [BidController::class, 'userBids']);
        Route::get('/user/test-drives', [TestDriveController::class, 'userTestDrives']);
        Route::get('/user/listings', [CarController::class, 'carListings']);
});

// Public routes
Route::get('/cars', [CarController::class, 'index']);
Route::get('/cars/{car}', [CarController::class, 'show']);



