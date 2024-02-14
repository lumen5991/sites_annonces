<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\RoleController;
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

    // TODO Revoir cette route (Voir la fonction du controller pour les instructions)
    Route::post('/verifyEmail', [AuthController::class, 'verifyEmail']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/resetPassword', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);
        
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::delete('/delete', [AuthController::class,'deleteUser']);

        Route::post('/edit', [AuthController::class,'updateUser']);

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/getAllUsers', [AuthController::class, 'getAllUsers']);
        });
    });

});


// Routes de catégorie

Route::prefix('category')->group(function () {

    Route::get("/getAll", [CategoryController::class, 'getAllCategories']);

    Route::middleware(['auth:sanctum', 'role:admin' ])->group(function () {

        Route::post("/add", [CategoryController::class, 'addCategory']);
        
        Route::get('/get/{id}', [CategoryController::class, "getCategory"]);

        Route::put("/edit/{id}", [CategoryController::class, 'editCategory']);

        Route::delete("/delete/{id}", [CategoryController::class, "deleteCategory"]);
    });

});

// Routes d'annonces
Route::prefix('announce')->group(function () {

    Route::get("/getAll", [AnnouncementController::class, "getAllAnnounce"]);

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post("/add", [AnnouncementController::class, "addAnnounce"]);

        Route::get("/get/{id}", [AnnouncementController::class,"getAnnouncement"]);

        Route::post("/edit/{id}", [AnnouncementController::class, "editAnnounce"]);
    
        Route::delete("/delete/{id}", [AnnouncementController::class,"deleteAnnounce"]);

        Route::get("/myAnnouncements", [AnnouncementController::class, "getMyAnnouncements"]);
    
    });

    

    

   
});


//Routes des notes
Route::middleware('auth:sanctum')->prefix('note')->group(function () {

    Route::post("/add/{announcement}", [NoteController::class, "addNote"]);

    Route::delete("/delete/{id}", [NoteController::class, "deleteNote"]);

    Route::get("/get/{id}", [NoteController::class, "getNote"]);   
    
    Route::get("/getAverageByAnnounce/{announcement}", [NoteController::class, "getAverageByAnnounce"]);
 
});


//Routes d'attribution des roles

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('roles')->group(function () {
    
    Route::post("/assignRoleAdmin/{id}", [RoleController::class, "assignAdminRole"]);

    Route::delete("/removeRole/{id}", [RoleController::class, "removeRole"]);
});





