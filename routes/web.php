<?php

use App\Http\Controllers\HuongDanBase;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

// TODO Route Public 
Route::get('/role3/login', [HuongDanBase::class, 'role3_login'])->name('role3.login')->middleware(RedirectIfAuthenticated::class);
Route::get('/admin/login', [HuongDanBase::class, 'admin_login'])->name('admin.login')->middleware(RedirectIfAuthenticated::class);

// TODO Route Admin
Route::middleware(['auth', 'roles:1'])->group(function () {
    Route::prefix('admin')->group(function () {

        // Route::get('/dashboard', function () {
        //     return view('admin.test_dashboard');
        // })->name('admin.dashboard');
        Route::get('/dashboard', [HuongDanBase::class, "index"])->name('admin.dashboard');
    });
});

// TODO Route Role3
Route::middleware(['auth', 'roles:2'])->group(function () {

    Route::get('/role3/dashboard', function () {
        return view('admin.role3_test_dashboard');
    })->name('role3.dashboard');
});
