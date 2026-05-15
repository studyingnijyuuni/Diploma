<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\MangaController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ScraperController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckScraperAbility;

Route::post('/register', [AuthController::class, 'register']); 
Route::post('/login', [AuthController::class, 'login']);
Route::get('/journals', [JournalController::class, 'index']);
Route::get('/journals/{id}', [JournalController::class, 'show']);
Route::get('/journals/{id}/mangas', [MangaController::class, 'getByJournal']);
Route::get('/search/mangas', [MangaController::class, 'search']);
Route::get('/mangas/{id}', [MangaController::class, 'show']);
// Route::get('/mangas/{id}', [MangaController::class, 'show']);

//Priviledged users (scraper)
Route::middleware(['auth:sanctum', CheckScraperAbility::class])->group(function () {
    
    Route::prefix('scraper')->group(function () {
        Route::get('/mangas', [ScraperController::class, 'getAllMangas']);
        Route::get('/journals', [ScraperController::class, 'getAllJournals']);
        Route::get('/journals/{journalId}/mangas', [ScraperController::class, 'getMangasByJournal']);
        Route::get('/journals/{journalId}/mangas/{amount}', [ScraperController::class, 'getNMangasByJournal']);
        Route::get('/favorites', [ScraperController::class, 'getFavoritedMangas']);
    });

    Route::put('/mangas/{manga}', [MangaController::class, 'update']);
    Route::post('/mangas', [MangaController::class, 'store']);
    Route::post('/journals', [JournalController::class, 'store']);
});

//Default users
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/auth/check', [AuthController::class, 'checkAuth']);
    
    // User Profile
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/email', [UserController::class, 'updateEmail']);
    
    // User Telegram
    Route::post('/user/telegram/code', [UserController::class, 'generateTelegramCode']); 
    Route::delete('/user/telegram', [UserController::class, 'unlinkTelegram']); 
    
    // User Favorites
    Route::get('/user/favorites', [UserController::class, 'favorites']);
    Route::post('/user/favorites', [UserController::class, 'followManga']);
    Route::delete('/user/favorites/{manga}', [UserController::class, 'unfollowManga']);
    Route::put('/user/favorites/{manga}/notifications', [UserController::class, 'setNotifications']);


});

