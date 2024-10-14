<?php

use App\Http\Controllers\Admin\Account\AccountController;
use App\Http\Controllers\Admin\Banner\{
    BannerController
};
use App\Http\Controllers\Custommer\{
    BannerController as CustommerBannerController,
    BookingController,
    ReviewsController,
    RommTypeController,
    ServiceController as CustommerServiceController
};
use App\Http\Controllers\Admin\Banner\index;
use App\Http\Controllers\Admin\Rooms\{
    RoomsController,
    RoomsTypeController
};
use App\Http\Controllers\Admin\Service\ServiceController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Custommer\UsersController;
use App\Http\Controllers\StatusController;
use App\Http\Middleware\Admin;
use App\Http\Middleware\PrivateCustommer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;

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

Route::prefix("/authentication")->middleware(StartSession::class)->group(function () {
    Route::post('/register', [AuthenticationController::class, "register"]);
    Route::post('/register-verification', [AuthenticationController::class, "registerVerification"]);
    Route::post('/login', [AuthenticationController::class, "login"]);
    Route::get('/logout', [AuthenticationController::class, "logout"]);
    Route::post('/forgot-password', [AuthenticationController::class, "forgotPassword"]);
    Route::post('/forgot-password-verification', [AuthenticationController::class, "forgotPasswordVerification"]);
});
Route::prefix("/customers")->group(function () {
    Route::get('/banner', [CustommerBannerController::class, "index"]);
    Route::get('/room-type', [RommTypeController::class, "index"]);
    Route::get('/room-type/{id}', [RommTypeController::class, "getById"]);
    Route::get('/reviews/{id}', [ReviewsController::class, "getByIdRoomType"]);
    Route::get('/reviews/average-rating/{id}', [ReviewsController::class, "getAverageRating"]);

    Route::middleware(PrivateCustommer::class)->group(function () {
        Route::post("/bookings", [BookingController::class, "addBooking"]);
        Route::put("/confirm-bookings/{id}", [BookingController::class, "confirmBookings"]);
        Route::get("/get-bookings", [BookingController::class, "getBookings"]);
        Route::get("/get-customer", [UsersController::class, "getUsers"]);
        Route::get("/get-service/{id}", [CustommerServiceController::class, "getServiceByIdRoomType"]);

    });
});

Route::prefix('/admin')->middleware(Admin::class)->group(function () {
    Route::prefix('/rooms')->group(function () {
        Route::get('/', [RoomsController::class, "index"]);
        Route::get('/{id}', [RoomsController::class, "getById"]);
        Route::post('/add', [RoomsController::class, "add"]);
        Route::put('/edit/{id}', [RoomsController::class, "edit"]);
        Route::delete('delete/{id}', [RoomsController::class, "delete"]);
    });
    Route::prefix('/rooms-type')->group(function () {
        Route::post('/add', [RoomsTypeController::class, "addRoomType"]);
        Route::get('/', [RoomsTypeController::class, "getRoomType"]);
        Route::get('/{id}', [RoomsTypeController::class, "getRoomTypeById"]);
        Route::post('edit/{id}', [RoomsTypeController::class, "editRoomType"]);
    });
    Route::prefix("/banner")->group(function () {
        Route::get("/", [BannerController::class, "index"]);
        Route::get("/{id}", [BannerController::class, "getId"]);
        Route::post("/add", [BannerController::class, "add"]);
        Route::put("/edit/{id}", [BannerController::class, "edit"]);
        Route::delete("/delete/{id}", [BannerController::class, "delete"]);
    });
    Route::prefix("account")->group(function () {
        Route::get("/", [AccountController::class, "index"]);
        Route::get("/{id}", [AccountController::class, "getId"]);
        Route::post("/add", [AccountController::class, "add"]);
        Route::put("/edit/{id}", [AccountController::class, "edit"]);
        Route::delete("/delete/{id}", [AccountController::class, "delete"]);
    });
    Route::prefix("service")->group(function () {
        Route::get("/", [ServiceController::class, "index"]);
        Route::get("/{id}", [ServiceController::class, "getId"]);
        Route::post("/add", [ServiceController::class, "add"]);
        Route::put("/edit/{id}", [ServiceController::class, "edit"]);
        Route::delete("/delete/{id}", [ServiceController::class, "delete"]);
    });
});

Route::get("/status", [StatusController::class, "index"]);
