<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservedSchedulesController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::get('/', function () {
//     return redirect('/dashboard');
// });

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/admin', function () {
    return redirect('/reservation-monitoring');
});

Route::get('/reservation-monitoring', function () {
    return Inertia::render('ReservationMonitoring', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->middleware(['auth', 'verified'])->name('reservation-monitoring');

// Route::get('/reservationmonitoring', function () {
//     return Inertia::render('ReservationMonitoring');
// })->middleware(['auth', 'verified'])->name('reservationmonitoring');

Route::get('/user-monitoring', function () {
    return Inertia::render('UserMonitoring');
})->middleware(['auth', 'verified', 'checkUserPosition:Admin'])->name('user-monitoring');

Route::get('/log-monitoring', function () {
    return Inertia::render('LogMonitoring');
})->middleware(['auth', 'verified', 'checkUserPosition:Admin'])->name('log-monitoring');

Route::post("/createReservation", [ReservedSchedulesController::class, 'createReservation']);
Route::get("/fetchMeetings", [ReservedSchedulesController::class, 'fetchMeetings']);
Route::get("/fetchAllReservations", [ReservedSchedulesController::class, 'fetchAllReservations']);
Route::post("/updateReservation", [ReservedSchedulesController::class, 'updateReservation']);
Route::get("/getTimeSlot", [ReservedSchedulesController::class, 'getTimeSlot']);
Route::get("/checkTimeRange", [ReservedSchedulesController::class, 'checkTimeRange']);
Route::post("/cancelReservation", [ReservedSchedulesController::class, 'cancelReservation']);
Route::get("/getUsers", [ReservedSchedulesController::class, 'getUsers']);
Route::post("/updateUser", [ReservedSchedulesController::class, 'updateUser']);
Route::post("/updateUserStatus", [ReservedSchedulesController::class, 'updateUserStatus']);
Route::get("/reservationReminder", [ReservedSchedulesController::class, 'reservationReminder']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/getLogs', [ReservedSchedulesController::class, 'getLogs']);


});


require __DIR__.'/auth.php';
