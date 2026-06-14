<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlantTypeController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\DiaryController;
use App\Http\Controllers\AdminController;

// public actions (dostepne bez logowania)
Route::get ('/', [PlantTypeController::class,'index'])->name('catalogue');   // strona startowa = katalog (gosc widzi od razu)
Route::get ('/catalogue', [PlantTypeController::class,'index']);                      // alias, zeby stare linki dzialaly
Route::get ('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'doLogin'])->name('login.post');
Route::get ('/register', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/register', [RegisterController::class, 'doRegister'])->name('register.post');

// protected actions (wymaga zalogowania)
Route::middleware('checklogin')->group(function () {
    Route::get('/dashboard', [HomeController::class,  'index'])->name('home');       // dashboard po zalogowaniu
    Route::get('/nickname', [LoginController::class, 'chooseNickname'])->name('nickname.show');
    Route::post('/nickname', [LoginController::class, 'saveNickname'])->name('nickname.save');
    Route::post('/logout',   [LoginController::class, 'doLogout'])->name('logout');

    // rosliny uzytkownika
    Route::get ('/my-plants', [PlantController::class, 'index'])->name('plants.index');
    Route::get ('/my-plants/create', [PlantController::class, 'create'])->name('plants.create');
    Route::post('/my-plants', [PlantController::class, 'store'])->name('plants.store');

    // pamietnik pielegnacji
    Route::get ('/diary', [DiaryController::class, 'index'])->name('diary.index');
    Route::post('/diary', [DiaryController::class, 'store'])->name('diary.store');

    // panel administratora — wymaga roli admin
    Route::middleware('checkadmin')->group(function () {
        Route::get ('/admin/plant-types',         [AdminController::class, 'plantTypes'])->name('admin.plant-types');
        Route::post('/admin/plant-types',         [AdminController::class, 'storePlantType'])->name('admin.plant-types.store');
        Route::post('/admin/plant-types/toggle',  [AdminController::class, 'togglePlantType'])->name('admin.plant-types.toggle');
        Route::get ('/admin/users',               [AdminController::class, 'users'])->name('admin.users');
        Route::post('/admin/users/role',          [AdminController::class, 'updateUserRole'])->name('admin.users.role');
    });
});
