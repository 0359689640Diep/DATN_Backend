<?php

use App\Http\Controllers\Custommer\{
    BannerController as CustommerBannerController,
    BookingController,
    ReviewsController,
    RommTypeController,
    ServiceController as CustommerServiceController,
    UsersController
};
use App\Http\Controllers\Admin\Banner\index;
use App\Http\Controllers\Admin\{
    RoomsController,
    RoomsTypeController,
    ServiceController,
    BannerController,
    AccountController,
    BookingController as AdminBookingController,
    BookingServiceUserController,
    ServiceUsers
};
use App\Http\Controllers\AuthenticationController;
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
        Route::get("/get-bookings/{id}", [BookingController::class, "getDetailBookings"]);
        Route::get("/get-customer", [UsersController::class, "getUsers"]);
        Route::post("/update-customer", [UsersController::class, "updateUsers"]);
        Route::get("/get-service/{id}", [CustommerServiceController::class, "getServiceByIdRoomType"]);
        Route::get('/reviews/bookings/{id}', [ReviewsController::class, "getByIdBooking"]);
        Route::post('/reviews/bookings', [ReviewsController::class, "postReviews"]);
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
        Route::post("/edit/{id}", [BannerController::class, "edit"]);
        Route::delete("/delete/{id}", [BannerController::class, "delete"]);
    });
    Route::prefix("account")->group(function () {
        Route::get("/", [AccountController::class, "index"]);
        Route::get("/{id}", [AccountController::class, "getId"]);
        Route::post("/add", [AccountController::class, "add"]);
        Route::post("/edit/{id}", [AccountController::class, "edit"]);
        Route::delete("/delete/{id}", [AccountController::class, "delete"]);
    });
    Route::prefix("service")->group(function () {
        Route::get("/", [ServiceController::class, "index"]);
        Route::get("/{id}", [ServiceController::class, "getId"]);
        Route::post("/add", [ServiceController::class, "add"]);
        Route::put("/edit/{id}", [ServiceController::class, "edit"]);
        Route::delete("/delete/{id}", [ServiceController::class, "delete"]);
    });
    Route::prefix("service-users")->group(function () {
        Route::get("/", [ServiceUsers::class, "index"]);
        Route::get("/{id}", [ServiceUsers::class, "getId"]);
        Route::get("/service/{id}", [ServiceUsers::class, "getIdService"]);
        Route::post("/add", [ServiceUsers::class, "add"]);
        Route::put("/edit/{id}", [ServiceUsers::class, "edit"]);
        Route::delete("/delete/{id}", [ServiceUsers::class, "delete"]);
    });
    Route::prefix("bookings")->group(function () {
        Route::get("/", [AdminBookingController::class, "index"]);
        Route::get("/{id}", [AdminBookingController::class, "getById"]);
        Route::post("/add", [AdminBookingController::class, "add"]);
        Route::put("/check-in-bookings/{id}", [AdminBookingController::class, "checkInBookings"]);
        Route::delete("/delete/{id}", [AdminBookingController::class, "delete"]);
    });
    Route::prefix("booking-service-users")->group(function () {
        Route::get("/", [BookingServiceUserController::class, "index"]);
        Route::get("/{id}", [BookingServiceUserController::class, "getById"]);
        Route::post("/add", [BookingServiceUserController::class, "addUsersToBookingsService"]);
        Route::put("/check-in-bookings/{id}", [BookingServiceUserController::class, "checkInBookings"]);
        Route::delete("/delete/{id}", [BookingServiceUserController::class, "delete"]);
    });
});

Route::get("/status", [StatusController::class, "index"]);
