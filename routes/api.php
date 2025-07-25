<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register/{type}',[AuthController::class,'register']);
Route::post('/login/{type}',[AuthController::class,'login']);
Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout',[AuthController::class,'logout']);
});

Route::middleware('auth:sanctum')->group(function(){
    Route::middleware('permission:view users')->group(function(){
        Route::apiResource('/users',UserController::class)->only(['index','show']);
    });
    Route::middleware('permission:edit users')->group(function(){
        Route::apiResource('/users',UserController::class)->only(['update']);
    });
    Route::middleware('permission:delete users')->group(function(){
        Route::apiResource('/users',UserController::class)->only(['destroy']);
    });
    Route::post('/users-search',[UserController::class,'search'])->middleware('auth:sanctum','permission:view users');
});

Route::apiResource('/categories',CategoryController::class)->only(['index','show']);
Route::middleware('auth:sanctum')->group(function(){
    Route::middleware('permission:create categories')->group(function(){
        Route::apiResource('/categories',CategoryController::class)->only(['store']);
    });
    Route::middleware('permission:edit categories')->group(function(){
        Route::apiResource('/categories',CategoryController::class)->only(['update']);
    });
    Route::middleware('permission:delete categories')->group(function(){
        Route::apiResource('/categories',CategoryController::class)->only(['destroy']);
    });
    Route::get('/categories/{category}/products',[CategoryController::class,'products'])->name('categories.products');
    Route::post('/categories-search',[CategoryController::class,'search'])->name('categories.search');
});

Route::apiResource('/products',ProductController::class)->only(['index','show']);
Route::middleware('auth:sanctum')->group(function(){
    Route::middleware('permission:create products')->group(function () {
        Route::apiResource('/products', ProductController::class)->only(['store']);
    });
    Route::middleware('permission:edit products')->group(function () {
        Route::apiResource('/products', ProductController::class)->only(['update']);
    });
    Route::middleware('permission:delete products')->group(function () {
        Route::apiResource('/products', ProductController::class)->only(['destroy']);
    });
    Route::post('/products-search',[ProductController::class,'search'])->name('products.search');
});


Route::middleware('auth:sanctum')->group(function(){
    Route::apiResource('/carts',CartController::class);
});

Route::post('/checkout', [CheckoutController::class, 'checkout'])->middleware('auth:sanctum');
Route::get('/orders', [CheckoutController::class, 'orderHistory'])->middleware('auth:sanctum');
Route::get('/orders/{order}', [CheckoutController::class, 'orderDetails'])->middleware('auth:sanctum');
Route::get('/all-orders', [CheckoutController::class, 'getAllOrders'])->middleware('auth:sanctum','permission:view orders');

Route::post('/payment/process/{order}', [PaymentController::class, 'paymentProcess'])->middleware('auth:sanctum');
Route::match(['GET','POST'],'/payment/callback', [PaymentController::class, 'callBack']);


Route::get('/roles', [RolePermissionController::class, 'getAllRoles'])->middleware('auth:sanctum','permission:view roles');
Route::get('/rolePermissions/{roleId}', [RolePermissionController::class, 'getRolePermissions'])->middleware('auth:sanctum','permission:view roles');
Route::post('/assignRole', [RolePermissionController::class, 'assignRoleToUser'])->middleware('auth:sanctum','permission:create roles');
Route::post('/createRole', [RolePermissionController::class, 'createNewRole'])->middleware('auth:sanctum','permission:create roles');
Route::post('/deleteRoleFromUser', [RolePermissionController::class, 'deleteRoleFromUser'])->middleware('auth:sanctum','permission:delete roles');
Route::get('/permissions', [RolePermissionController::class, 'getAllPermissions'])->middleware('auth:sanctum','permission:view permissions');
Route::post('assignPermissionToRole', [RolePermissionController::class, 'assignPermissionToRole'])->middleware('auth:sanctum','permission:assign permissions');
Route::post('revokePermissionFromRole', [RolePermissionController::class, 'removePermissionFromRole'])->middleware('auth:sanctum','permission:assign permissions');
Route::post('assignPermissionToUser', [RolePermissionController::class, 'assignPermissionToUser'])->middleware('auth:sanctum','permission:assign permissions');
Route::post('revokePermissionFromUser', [RolePermissionController::class, 'removePermissionFromUser'])->middleware('auth:sanctum','permission:assign permissions');
