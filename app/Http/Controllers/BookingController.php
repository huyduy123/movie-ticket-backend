<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use App\Models\Seat;
use App\Models\Movie;
use App\Models\Theater;
use App\Models\Showtime;
use App\Models\Combo;

class BookingController extends Controller
{
    public function __construct()
    {
        // Sử dụng middleware auth đúng cách
        auth()->shouldUse('web');
    }

    public function index()
    {
        return response()->json(Booking::all(), 200);
    }

    public function store(Request $request)
    {
        // Kiểm tra nếu user chưa đăng nhập
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để đặt vé.');
        }

        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'theater_id' => 'required|exists:theaters,id',
            'movie_id' => 'required|exists:movies,id',
            'date' => 'required|date',
            'showtime_id' => 'required|exists:showtimes,id',
        ]);

        // Tạo booking mới
        $booking = Booking::create([
            'user_id' => Auth::id(),
            'theater_id' => $request->theater_id,
            'movie_id' => $request->movie_id,
            'date' => $request->date,
            'showtime_id' => $request->showtime_id,
            'status' => 'pending',
        ]);

        // Chuyển hướng đến trang chọn ghế
        return redirect()->route('select_seats', ['booking_id' => $booking->id]);
    }

    public function selectSeats(Request $request)
    {
        $theater_id = $request->theater_id;
        $movie_id = $request->movie_id;
        $date = $request->date;
        $showtime_id = $request->showtime_id;

        // Kiểm tra xem các trường có đầy đủ không
        if (!$theater_id || !$movie_id || !$date || !$showtime_id) {
            return back()->with('error', 'Vui lòng chọn đầy đủ Rạp, Ngày và Suất chiếu.');
        }

        // Lấy thông tin phim, rạp, suất chiếu
        $movie = Movie::find($movie_id);
        $theater = Theater::find($theater_id);
        $showtime = Showtime::find($showtime_id);

        // Kiểm tra nếu không có suất chiếu hợp lệ
        if (!$movie || !$theater || !$showtime) {
            return back()->with('error', 'Thông tin rạp, ngày hoặc suất chiếu không hợp lệ.');
        }

        // ❗️Lấy screen_id theo theater_id
        $screen = \App\Models\Screen::where('theater_id', $theater_id)->first();

        if (!$screen) {
            return back()->with('error', 'Không tìm thấy màn hình phù hợp cho rạp này.');
        }
        $standard_seats = Seat::where('screen_id', $screen->id)->where('seat_type', 'standard')->get();
        $vip_seats = Seat::where('screen_id', $screen->id)->where('seat_type', 'VIP')->get();
        $seats = $standard_seats->merge($vip_seats); // 👈 Thêm dòng này

        $combos = Combo::all();

        return view('select_seats', compact(
            'movie',
            'theater',
            'showtime',
            'date',
            'standard_seats',
            'vip_seats',
            'theater_id',
            'movie_id',
            'showtime_id',
            'combos',
            'seats' // 👈 Thêm dòng này
        ));
    }



    public function show($id)
    {
        return response()->json(Booking::findOrFail($id), 200);
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update($request->all());
        return response()->json($booking, 200);
    }

    public function destroy($id)
    {
        Booking::destroy($id);
        return response()->json(['message' => 'Booking deleted'], 200);
    }

    public function confirmSeats(Request $request)
    {
        // Lấy dữ liệu từ request
        $selectedSeats = $request->input('seats', []);
        $movie_id = $request->input('movie_id');
        $theater_id = $request->input('theater_id');
        $showtime_id = $request->input('showtime_id');
        $date = $request->input('date');
        $comboInputs = $request->input('combo', []);
        $totalAmount = $request->input('total_amount');

        // Validate cơ bản
        if (!$movie_id || !$theater_id || !$showtime_id || !$date || empty($selectedSeats)) {
            return redirect()->back()->withErrors('Thiếu thông tin đầu vào. Vui lòng chọn đầy đủ!');
        }

        try {
            // Lấy dữ liệu từ DB
            $movie = Movie::findOrFail($movie_id);
            $theater = Theater::findOrFail($theater_id);
            $showtime = Showtime::findOrFail($showtime_id);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Không thể tìm thấy dữ liệu. Vui lòng thử lại!');
        }

        // Xử lý combo
        $selectedComboDetails = [];
        foreach ($comboInputs as $comboId => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity > 0) {
                $combo = Combo::find($comboId);
                if ($combo) {
                    $selectedComboDetails[] = [
                        'combo' => $combo,
                        'quantity' => $quantity,
                        'total_price' => $combo->price * $quantity,
                    ];
                }
            }
        }

        // Trả về view xác nhận ghế
        return view('confirm_seats', [
            'selectedSeats' => $selectedSeats,
            'movie' => $movie,
            'theater' => $theater,
            'date' => $date,
            'showtime' => $showtime,
            'selectedComboDetails' => $selectedComboDetails,
            'totalAmount' => $totalAmount,
        ]);
    }
}
