<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\NoteController;
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

// Routes d'authentification
Route::prefix('user')->group(function () {

    Route::post('/register', [AuthController::class, 'createUser']);

     //TODO Revoir cette route (Voir la fonction du controller pour les instructions)
    Route::post('/verifyEmail', [AuthController::class, 'verifyEmail']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/resetPassword', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);
        
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::delete('/delete', [AuthController::class,'deleteUser']);

        Route::post('/edit', [AuthController::class,'updateUser']);
    });

});


// Routes de catégorie
Route::middleware('auth:sanctum')->prefix('category')->group(function () {

    Route::post("/add", [CategoryController::class, 'addCategory']);

    Route::get("/getAll", [CategoryController::class, 'getAllCategories']);

    Route::get('/get/{id}', [CategoryController::class, "getCategory"]);

    Route::put("/edit/{id}", [CategoryController::class, 'editCategory']);

    Route::delete("/delete/{id}", [CategoryController::class, "deleteCategory"]);

});

// Routes d'annonces
Route::middleware('auth:sanctum')->prefix('announce')->group(function () {

    Route::post("/add", [AnnouncementController::class, "addAnnounce"]);

    Route::get("/getAll", [AnnouncementController::class,"getAllAnnounce"]);

    Route::get("/get/{id}", [AnnouncementController::class,"getAnnouncement"]);

    Route::post("/edit/{id}", [AnnouncementController::class, "editAnnounce"]);

    Route::delete("/delete/{id}", [AnnouncementController::class,"deleteAnnounce"]);

});

//Routes des notes
Route::middleware('auth:sanctum')->prefix('note')->group(function () {

    Route::post("/add/{announcement}", [NoteController::class, "addNote"]);

    Route::post("/edit/{id}", [NoteController::class,"updateNote"]);

    Route::delete("/delete/{id}", [NoteController::class,"deleteNote"]);

});


