<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth; // ✅ Đúng
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    // Hiển thị danh sách giao dịch thanh toán
    public function showPayments(Request $request)
    {
        $query = Payment::with('booking');

        // Lọc theo phương thức thanh toán
        if ($request->has('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        // Lọc theo trạng thái thanh toán
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Lọc theo ngày thanh toán
        if ($request->has('payment_date') && !empty($request->payment_date)) {
            $query->whereDate('created_at', $request->payment_date);
        }

        // Phân trang danh sách thanh toán
        $payments = $query->paginate(10);

        return view('admin.payments', [
            'payments' => $payments,
            'payment_method' => $request->payment_method, // Truyền lại giá trị phương thức thanh toán
            'status' => $request->status, // Truyền lại giá trị trạng thái
            'payment_date' => $request->payment_date // Truyền lại giá trị ngày thanh toán
        ]);
    }

    // Cập nhật trạng thái thanh toán
    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return redirect()->route('admin.payments')->with('error', 'Không tìm thấy giao dịch thanh toán.');
        }

        // Xác nhận rằng trạng thái được gửi đúng
        $request->validate([
            'status' => 'required|in:pending,completed,failed',
        ]);

        // Cập nhật trạng thái thanh toán
        $payment->update([
            'status' => $request->status,
        ]);

        return redirect()->route('admin.payments')->with('success', 'Cập nhật trạng thái thanh toán thành công!');
    }

    // Xóa giao dịch thanh toán
    public function destroy($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return redirect()->route('admin.payments')->with('error', 'Không tìm thấy giao dịch thanh toán.');
        }

        // Xóa giao dịch thanh toán
        $payment->delete();
        return redirect()->route('admin.payments')->with('success', 'Giao dịch thanh toán đã được xóa!');
    }

    public function paymentWithPayOS(Request $request)
    {
        $userId = Auth::id();

        $booking = Booking::create([
            'user_id' => $userId,
            'showtime_id' => $request->showtime_id,
            'total_price' => $request->total_amount,
            'status' => 'pending',
        ]);

        $orderId = 'BOOKING_' . $booking->id;
        $amount = (int) $booking->total_price;

        $clientId = env('PAYOS_CLIENT_ID');
        $apiKey = env('PAYOS_API_KEY');
        $checksumKey = env('PAYOS_CHECKSUM_KEY');

        $extraData = json_encode($request->seats, JSON_UNESCAPED_UNICODE);
        $expiredAt = now()->addMinutes(10)->timestamp;

        $returnUrl = route('payment.success');
        $cancelUrl = route('payment.cancel');

        // 👉 raw signature theo định dạng PayOS
        $rawSignature = implode('', [
            $orderId,
            $amount,
            'Thanh toán đơn hàng đặt vé xem phim',
            $returnUrl,
            $cancelUrl,
            $extraData,
            $expiredAt
        ]);

        $signature = hash_hmac('sha256', $rawSignature, $checksumKey);

        $payload = [
            'orderCode' => $orderId,
            'amount' => $amount,
            'description' => 'Thanh toán đơn hàng đặt vé xem phim',
            'buyerName' => 'Nguyen Van A',
            'buyerEmail' => 'nguyenvana@example.com',
            'buyerPhone' => '0901234567',
            'buyerAddress' => '123 Nguyễn Huệ, Quận 1, TP.HCM',
            'items' => [
                [
                    'name' => 'Vé xem phim',
                    'quantity' => count($request->seats),
                    'price' => $amount,
                ]
            ],
            'returnUrl' => route('payment.success'),
            'cancelUrl' => route('payment.cancel'),
            'extraData' => json_encode($request->seats),
            'expiredAt' => now()->addMinutes(10)->timestamp,
        ];


        $response = Http::withHeaders([
            'x-client-id' => $clientId,
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api-merchant.payos.vn/v2/payment-requests', $payload);

        if ($response->failed()) {
            return redirect()->back()->with('error', 'Không thể tạo đơn hàng thanh toán.');
        }

        $responseData = $response->json();

        if (!isset($responseData['checkoutUrl'])) {
            return redirect()->route('payment.payos')->with('error', 'Không nhận được link thanh toán từ PayOS.');
        }


        return view('payment.payos', [
            'checkoutUrl' => $responseData['checkoutUrl'],
            'orderId' => $orderId,
            'amount' => $amount,
        ]);
    }


    public function handlePayOSWebhook(Request $request)
    {
        $orderCode = $request->input('orderCode');
        $status = $request->input('status'); // SUCCEEDED, CANCELLED

        if (!str_starts_with($orderCode, 'BOOKING_')) {
            return response('Invalid Order Code', 400);
        }

        $bookingId = (int) str_replace('BOOKING_', '', $orderCode);
        $booking = Booking::find($bookingId);
        if (!$booking) return response('Booking not found', 404);

        if ($status === 'SUCCEEDED') {
            $booking->status = 'paid';
            $booking->save();

            // Thêm dữ liệu vào booking_details từ danh sách seat_id
            $seatIds = json_decode($request->input('extraData'));
            foreach ($seatIds as $seatId) {
                $seat = \App\Models\Seat::find($seatId);
                if ($seat) {
                    DB::table('booking_details')->insert([
                        'booking_id' => $booking->id,
                        'seat_id' => $seat->id,
                        'price' => $seat->price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } elseif ($status === 'CANCELLED') {
            $booking->status = 'canceled';
            $booking->save();
        }

        return response('OK', 200);
    }


    public function paymentSuccess()
    {
        return view('payment.success');
    }

    public function paymentCancel()
    {
        return view('payment.cancel');
    }
}
