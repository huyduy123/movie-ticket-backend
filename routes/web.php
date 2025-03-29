<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ShowtimeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FooterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\DashBoardController;
use App\Http\Controllers\LoginGoogleController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\SeatController;

Route::get('/', function () {
    return view('welcome'); // hoặc view khác nếu bạn có
});
Route::get('/auth/google', [LoginGoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [LoginGoogleController::class, 'handleGoogleCallback']);


// 🏠 Route Trang Chủ
Route::get('/home', [MovieController::class, 'index'])->name('home');


// 🟢 Authentication (Đăng nhập, Đăng ký)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// 🔴 Đăng xuất (POST thay vì GET để bảo mật tốt hơn)
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');


Route::middleware(['auth'])->group(function () {
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
});
// 📌 Footer Data
Route::get('/footer', [FooterController::class, 'footerData']);
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [DashBoardController::class, 'index'])->name('admin.dashboard');

    Route::get('/movies/movies', [MovieController::class, 'showMovie'])->name('admin.movies.movies');
    Route::get('/movies/create', [MovieController::class, 'create'])->name('admin.movies.addmovies');
    Route::post('/movies/store', [MovieController::class, 'store'])->name('admin.movies.store');
    Route::get('/movies/edit/{id}', [MovieController::class, 'edit'])->name('admin.movies.edit');
    Route::put('/movies/update/{id}', [MovieController::class, 'update'])->name('admin.movies.update');
    Route::delete('/movies/delete/{id}', [MovieController::class, 'destroy'])->name('admin.movies.destroy');


    Route::get('/showtimes', [ShowtimeController::class, 'index'])->name('admin.showtimes.showtimes');
    Route::get('/add', [ShowtimeController::class, 'create'])->name('admin.showtimes.addshowtime');
    Route::post('/showtimes/store', [ShowtimeController::class, 'store'])->name('admin.showtimes.store');
    Route::get('/showtimes/edit/{id}', [ShowtimeController::class, 'edit'])->name('admin.showtimes.edit');
    Route::put('/showtimes/update/{id}', [ShowtimeController::class, 'update'])->name('admin.showtimes.update');
    Route::delete('/delete/{id}', [ShowtimeController::class, 'deleteShowtime'])->name('admin.showtimes.destroy');

    // 🎟️ Quản lý Vé (Cập nhật URL giống movie)
    Route::get('/admin/tickets', [TicketController::class, 'showTickets'])->name('admin.tickets');
    Route::post('/admin/tickets/store', [TicketController::class, 'store'])->name('admin.tickets.store');
    Route::post('/admin/tickets/{id}/update', [TicketController::class, 'update'])->name('admin.tickets.update');
    Route::post('/admin/tickets/{id}/delete', [TicketController::class, 'destroy'])->name('admin.tickets.destroy');


    Route::get('/admin/payments', [PaymentController::class, 'showPayments'])->name('admin.payments');
    Route::post('/admin/payments/{id}/update', [PaymentController::class, 'update'])->name('admin.payments.update');
    Route::post('/admin/payments/{id}/delete', [PaymentController::class, 'destroy'])->name('admin.payments.destroy');

    // Route quản lý người dùng
    Route::get('/admin/users', [UserController::class, 'list'])->name('admin.users'); // Hiển thị form thêm người dùng
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create'); // Hiển thị form tạo người dùng
    Route::post('/admin/users/store', [UserController::class, 'store'])->name('admin.users.store'); // Lưu người dùng mới
    // Route hiển thị form chỉnh sửa thông tin người dùng

    Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    // Đảm bảo sử dụng PUT hoặc PATCH cho cập nhật
    Route::put('/admin/users/{id}/update', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}/delete', [UserController::class, 'destroy'])->name('admin.users.delete');


    Route::get('/reports', function () {
        return view('admin.reports');
    })->name('admin.reports');
});
Route::middleware('auth')->group(function () {
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/chon-ghe/{booking_id}', [BookingController::class, 'selectSeats'])->name('select_seats');
    Route::get('/chon-ghe', [BookingController::class, 'selectSeats'])->name('select_seats.no_id');

    Route::get('/seats', [SeatController::class, 'index']);
    Route::get('/seats/{showtime_id}', [SeatController::class, 'showSeats'])->name('seats.select');
    //Confirm
    Route::post('/confirm-seats', [BookingController::class, 'confirmSeats'])->name('confirm.seats');

    Route::post('/payos', [PaymentController::class, 'paymentWithPayOS'])->name('payment.payos');
    Route::post('/webhook/payos', [PaymentController::class, 'handlePayOSWebhook']);
    Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');
});
//Movie
Route::get('/movies/{id}', [MovieController::class, 'show'])->name('movies.show');
