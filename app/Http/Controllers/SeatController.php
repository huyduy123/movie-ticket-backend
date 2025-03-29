<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\Showtime; // ✅ Bổ sung dòng này
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function showSeatsByShowtime($showtime_id)
    {
        $showtime = Showtime::findOrFail($showtime_id);

        $standard_seats = Seat::where('screen_id', $showtime->screen_id)
            ->where('seat_type', 'standard')->get();

        $vip_seats = Seat::where('screen_id', $showtime->screen_id)
            ->where('seat_type', 'VIP')->get();

        $seats = $standard_seats->merge($vip_seats); // ✅ Gộp lại tất cả ghế

        return view('select_seats', [
            'movie_id' => $showtime->movie_id,
            'theater_id' => $showtime->theater_id,
            'date' => now()->toDateString(),
            'showtime_id' => $showtime_id,
            'seats' => $seats, // ✅ Truyền biến $seats cho view
        ]);
    }
}
