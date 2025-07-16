<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceItemController;
use App\Http\Controllers\Api\ServiceCenterController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TechnicianController;

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

// Named login route for Laravel's authentication system
Route::get('/login', function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Unauthenticated. Please login to access this resource.',
        'code' => 401
    ], 401);
})->name('login');

// Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Rei do Óleo API is running',
        'timestamp' => now(),
        'version' => env('API_VERSION', 'v1')
    ]);
});

// Public Routes (não precisam de autenticação)
Route::prefix('v1')->group(function () {
    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:5,1');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
            ->middleware('throttle:5,1');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])
            ->middleware('throttle:5,1');

        // Email Verification
        Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('/email/verification-notification', [AuthController::class, 'sendVerification'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });
});

// Protected Routes (precisam de autenticação)
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // Authentication Routes (Authenticated)
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/me', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
    });

    // =============================================================================
    // CLIENT ROUTES
    // =============================================================================
    Route::prefix('clients')->group(function () {
        // CRUD básico
        Route::get('/', [ClientController::class, 'index']);                    // GET /api/v1/clients
        Route::post('/', [ClientController::class, 'store']);                   // POST /api/v1/clients
        Route::get('/{id}', [ClientController::class, 'show']);                 // GET /api/v1/clients/{id}
        Route::put('/{id}', [ClientController::class, 'update']);               // PUT /api/v1/clients/{id}
        Route::delete('/{id}', [ClientController::class, 'destroy']);           // DELETE /api/v1/clients/{id}

        // Busca específica
        Route::post('/search/document', [ClientController::class, 'searchByDocument']);  // POST /api/v1/clients/search/document
        Route::post('/search/phone', [ClientController::class, 'searchByPhone']);        // POST /api/v1/clients/search/phone
    });

    // =============================================================================
    // VEHICLE ROUTES
    // =============================================================================
    Route::prefix('vehicles')->group(function () {
        // CRUD básico
        Route::get('/', [VehicleController::class, 'index']);                   // GET /api/v1/vehicles
        Route::post('/', [VehicleController::class, 'store']);                  // POST /api/v1/vehicles
        Route::get('/{id}', [VehicleController::class, 'show']);                // GET /api/v1/vehicles/{id}
        Route::put('/{id}', [VehicleController::class, 'update']);              // PUT /api/v1/vehicles/{id}
        Route::delete('/{id}', [VehicleController::class, 'destroy']);          // DELETE /api/v1/vehicles/{id}

        // Busca específica
        Route::post('/search/license-plate', [VehicleController::class, 'searchByLicensePlate']); // POST /api/v1/vehicles/search/license-plate
        Route::get('/client/{clientId}', [VehicleController::class, 'getByClient']);              // GET /api/v1/vehicles/client/{clientId}
        Route::put('/{id}/mileage', [VehicleController::class, 'updateMileage']);                 // PUT /api/v1/vehicles/{id}/mileage

        // Analytics e relatórios
        Route::get('/dashboard/stats', [VehicleController::class, 'getDashboardStats']);          // GET /api/v1/vehicles/dashboard/stats
        Route::get('/chart-data', [VehicleController::class, 'getChartData']);                    // GET /api/v1/vehicles/chart-data
        Route::get('/recent', [VehicleController::class, 'getRecentVehicles']);                   // GET /api/v1/vehicles/recent
        Route::get('/service-stats', [VehicleController::class, 'getVehiclesWithServiceStats']);  // GET /api/v1/vehicles/service-stats
        Route::get('/performance-metrics', [VehicleController::class, 'getPerformanceMetrics']);  // GET /api/v1/vehicles/performance-metrics
    });

    // =============================================================================
    // CATEGORY ROUTES
    // =============================================================================
    Route::prefix('categories')->group(function () {
        // CRUD básico
        Route::get('/', [CategoryController::class, 'index']);                  // GET /api/v1/categories
        Route::post('/', [CategoryController::class, 'store']);                 // POST /api/v1/categories
        Route::get('/{id}', [CategoryController::class, 'show']);               // GET /api/v1/categories/{id}
        Route::put('/{id}', [CategoryController::class, 'update']);             // PUT /api/v1/categories/{id}
        Route::delete('/{id}', [CategoryController::class, 'destroy']);         // DELETE /api/v1/categories/{id}

        // Listagem específica
        Route::get('/active/list', [CategoryController::class, 'getActive']);   // GET /api/v1/categories/active/list
    });

    // =============================================================================
    // PRODUCT ROUTES
    // =============================================================================
    Route::prefix('products')->group(function () {
        // CRUD básico
        Route::get('/', [ProductController::class, 'index']);                   // GET /api/v1/products
        Route::post('/', [ProductController::class, 'store']);                  // POST /api/v1/products
        Route::get('/{id}', [ProductController::class, 'show']);                // GET /api/v1/products/{id}
        Route::put('/{id}', [ProductController::class, 'update']);              // PUT /api/v1/products/{id}
        Route::delete('/{id}', [ProductController::class, 'destroy']);          // DELETE /api/v1/products/{id}

        // Listagens específicas
        Route::get('/active/list', [ProductController::class, 'getActive']);                     // GET /api/v1/products/active/list
        Route::get('/category/{categoryId}', [ProductController::class, 'getByCategory']);       // GET /api/v1/products/category/{categoryId}
        Route::get('/stock/low', [ProductController::class, 'getLowStock']);                     // GET /api/v1/products/stock/low
        Route::post('/search/name', [ProductController::class, 'searchByName']);                 // POST /api/v1/products/search/name

        // Ações específicas
        Route::put('/{id}/stock', [ProductController::class, 'updateStock']);                    // PUT /api/v1/products/{id}/stock

        // Analytics e relatórios
        Route::get('/with-sales-data', [ProductController::class, 'withSalesData']);             // GET /api/v1/products/with-sales-data
        Route::get('/performance-metrics', [ProductController::class, 'performanceMetrics']);     // GET /api/v1/products/performance-metrics
        Route::get('/chart-data', [ProductController::class, 'chartData']);                      // GET /api/v1/products/chart-data
    });

    // =============================================================================
    // SERVICE CENTER ROUTES
    // =============================================================================
    Route::prefix('service-centers')->group(function () {
        // CRUD básico
        Route::get('/', [ServiceCenterController::class, 'index']);             // GET /api/v1/service-centers
        Route::post('/', [ServiceCenterController::class, 'store']);            // POST /api/v1/service-centers
        Route::get('/{id}', [ServiceCenterController::class, 'show']);          // GET /api/v1/service-centers/{id}
        Route::put('/{id}', [ServiceCenterController::class, 'update']);        // PUT /api/v1/service-centers/{id}
        Route::delete('/{id}', [ServiceCenterController::class, 'destroy']);    // DELETE /api/v1/service-centers/{id}

        // Listagens específicas
        Route::get('/active/list', [ServiceCenterController::class, 'getActive']);               // GET /api/v1/service-centers/active/list
        Route::get('/main-branch/get', [ServiceCenterController::class, 'getMainBranch']);       // GET /api/v1/service-centers/main-branch/get

        // Busca específica
        Route::post('/search/code', [ServiceCenterController::class, 'findByCode']);             // POST /api/v1/service-centers/search/code
        Route::post('/search/region', [ServiceCenterController::class, 'getByRegion']);          // POST /api/v1/service-centers/search/region
        Route::post('/search/nearby', [ServiceCenterController::class, 'findNearby']);           // POST /api/v1/service-centers/search/nearby
    });

    // =============================================================================
    // SERVICE ROUTES
    // =============================================================================
    Route::prefix('services')->group(function () {
        // CRUD básico
        Route::get('/', [ServiceController::class, 'index']);                   // GET /api/v1/services
        Route::post('/', [ServiceController::class, 'store']);                  // POST /api/v1/services
        Route::get('/{id}', [ServiceController::class, 'show']);                // GET /api/v1/services/{id}
        Route::put('/{id}', [ServiceController::class, 'update']);              // PUT /api/v1/services/{id}
        Route::delete('/{id}', [ServiceController::class, 'destroy']);          // DELETE /api/v1/services/{id}

        // Listagens específicas
        Route::get('/service-center/{serviceCenterId}', [ServiceController::class, 'getByServiceCenter']); // GET /api/v1/services/service-center/{serviceCenterId}
        Route::get('/client/{clientId}', [ServiceController::class, 'getByClient']);                       // GET /api/v1/services/client/{clientId}
        Route::get('/vehicle/{vehicleId}', [ServiceController::class, 'getByVehicle']);                    // GET /api/v1/services/vehicle/{vehicleId}
        Route::get('/technician/{technicianId}', [ServiceController::class, 'getByTechnician']);           // GET /api/v1/services/technician/{technicianId}

        // Busca específica
        Route::post('/search/service-number', [ServiceController::class, 'searchByServiceNumber']);        // POST /api/v1/services/search/service-number

        // Ações específicas
        Route::put('/{id}/status', [ServiceController::class, 'updateStatus']);                            // PUT /api/v1/services/{id}/status
        Route::get('/dashboard/stats', [ServiceController::class, 'getDashboardStats']);                   // GET /api/v1/services/dashboard/stats
    });

    // =============================================================================
    // DASHBOARD ROUTES
    // =============================================================================
    Route::prefix('dashboard')->group(function () {
        Route::get('/overview', [DashboardController::class, 'getOverview']);                              // GET /api/v1/dashboard/overview
        Route::get('/charts', [DashboardController::class, 'getCharts']);                                   // GET /api/v1/dashboard/charts
        Route::get('/alerts', [DashboardController::class, 'getAlerts']);                                   // GET /api/v1/dashboard/alerts
    });

    // =============================================================================
    // SERVICE ITEM ROUTES (Nested under services)
    // =============================================================================
    Route::prefix('services/{serviceId}/items')->group(function () {
        // CRUD básico para itens do serviço
        Route::get('/', [ServiceItemController::class, 'index']);               // GET /api/v1/services/{serviceId}/items
        Route::post('/', [ServiceItemController::class, 'store']);              // POST /api/v1/services/{serviceId}/items
        Route::get('/{itemId}', [ServiceItemController::class, 'show']);        // GET /api/v1/services/{serviceId}/items/{itemId}
        Route::put('/{itemId}', [ServiceItemController::class, 'update']);      // PUT /api/v1/services/{serviceId}/items/{itemId}
        Route::delete('/{itemId}', [ServiceItemController::class, 'destroy']);  // DELETE /api/v1/services/{serviceId}/items/{itemId}

        // Ações específicas
        Route::post('/bulk', [ServiceItemController::class, 'bulkStore']);      // POST /api/v1/services/{serviceId}/items/bulk
        Route::get('/total/calculate', [ServiceItemController::class, 'getServiceTotal']); // GET /api/v1/services/{serviceId}/items/total/calculate
    });

    // =============================================================================
    // TECHNICIAN ROUTES
    // =============================================================================
    Route::prefix('technician')->group(function () {
        // Busca e dashboard
        Route::post('/search', [TechnicianController::class, 'search']);                        // POST /api/v1/technician/search
        Route::get('/dashboard', [TechnicianController::class, 'dashboard']);                   // GET /api/v1/technician/dashboard

        // Serviços
        Route::post('/services', [TechnicianController::class, 'createService']);               // POST /api/v1/technician/services
        Route::get('/services/my', [TechnicianController::class, 'myServices']);                // GET /api/v1/technician/services/my
        Route::put('/services/{id}/status', [TechnicianController::class, 'updateServiceStatus']); // PUT /api/v1/technician/services/{id}/status
    });

    // =============================================================================
    // USER ROUTES
    // =============================================================================
    Route::prefix('users')->group(function () {
        // CRUD básico
        Route::get('/', [UserController::class, 'index']);                      // GET /api/v1/users
        Route::post('/', [UserController::class, 'store']);                     // POST /api/v1/users
        Route::get('/{id}', [UserController::class, 'show']);                   // GET /api/v1/users/{id}
        Route::put('/{id}', [UserController::class, 'update']);                 // PUT /api/v1/users/{id}
        Route::delete('/{id}', [UserController::class, 'destroy']);             // DELETE /api/v1/users/{id}

        // Listagens específicas
        Route::get('/active/list', [UserController::class, 'getActive']);                       // GET /api/v1/users/active/list
        Route::get('/service-center/{serviceCenterId}', [UserController::class, 'getByServiceCenter']); // GET /api/v1/users/service-center/{serviceCenterId}
        Route::get('/role/{role}', [UserController::class, 'getByRole']);                       // GET /api/v1/users/role/{role}

        // Ações específicas
        Route::put('/{id}/last-login', [UserController::class, 'updateLastLogin']);             // PUT /api/v1/users/{id}/last-login
        Route::put('/{id}/change-password', [UserController::class, 'changePassword']);         // PUT /api/v1/users/{id}/change-password
    });
});

// Fallback Route
Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Endpoint not found',
        'available_endpoints' => [
            'health' => 'GET /api/health',
            'auth' => 'POST /api/v1/auth/*',
            'clients' => 'GET|POST|PUT|DELETE /api/v1/clients/*',
            'vehicles' => 'GET|POST|PUT|DELETE /api/v1/vehicles/*',
            'categories' => 'GET|POST|PUT|DELETE /api/v1/categories/*',
            'products' => 'GET|POST|PUT|DELETE /api/v1/products/*',
            'service-centers' => 'GET|POST|PUT|DELETE /api/v1/service-centers/*',
            'services' => 'GET|POST|PUT|DELETE /api/v1/services/*',
            'service-items' => 'GET|POST|PUT|DELETE /api/v1/services/{serviceId}/items/*',
            'users' => 'GET|POST|PUT|DELETE /api/v1/users/*'
        ],
        'code' => 404
    ], 404);
});
