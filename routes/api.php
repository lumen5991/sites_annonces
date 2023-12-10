<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AnnouncementController;
use Illuminate\Support\Facades\Route;

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
//Utilisateur

 Route::post('/auth/register', [AuthController::class, 'createUser']);

 Route::post('auth/verifyEmail/{email}', [AuthController::class,'verifyEmail']);

 Route::post('/auth/login', [AuthController::class, 'login']);
 
 Route::get('auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
 
 Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
 
 Route::post('/auth/resetPassword', [AuthController::class,'resetPassword']);



//Catégorie

Route::post("/addCategory", [CategoryController::class,'addCategory']);

Route::get("/getAllCategories", [CategoryController::class,'getAllCategories']);

Route::post("/editCategory", [CategoryController::class,'editCategory']);

Route::post("/deleteCategory", [CategoryController::class,"deleteCategory"]);



//Annonces

Route::post("/addAnnounce", [AnnouncementController::class, "addAnnounce"])->middleware("auth:sanctum");