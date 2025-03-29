{{-- resources/views/confirm.blade.php --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận Đặt Vé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/style.css', 'resources/css/header.css', 'resources/css/footer.css'])
    <style>
        body { background-color: #111; color: #fff; }
        .info-box {
            background: #1e1e1e;
            border: 1px solid #ffc107;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 0 15px rgba(255,193,7,0.2);
        }
        .sub-box {
            background-color: #222;
            border: 1px solid #444;
            padding: 1rem;
            border-radius: 1rem;
        }
        .highlight { color: #ffc107; }
    </style>
</head>
<body>
@include('layouts.header')

<main class="py-5">
    <div class="container">
        <h2 class="text-center text-warning mb-5">
            <i class="fas fa-ticket-alt me-2"></i> Xác Nhận Thông Tin Đặt Vé
        </h2>

        <!-- THÔNG TIN VÉ -->
        <div class="info-box mb-5">
            <h4 class="text-warning mb-4"><i class="fas fa-film me-2"></i>Thông Tin Vé</h4>
            <div class="row g-4">
                <div class="col-md-4">
                    <img src="{{ asset('images/' . $movie->image) }}" class="img-fluid rounded shadow" alt="{{ $movie->title }}">
                </div>
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-6 sub-box">
                            <i class="fas fa-film highlight me-2"></i><strong>Phim:</strong> {{ $movie->title }}
                        </div>
                        <div class="col-6 sub-box">
                            <i class="fas fa-building highlight me-2"></i><strong>Rạp:</strong> {{ $theater->name }}
                        </div>
                        <div class="col-6 sub-box">
                            <i class="fas fa-calendar-alt highlight me-2"></i><strong>Ngày:</strong> {{ date('d-m-Y', strtotime($date)) }}
                        </div>
                        <div class="col-6 sub-box">
                            <i class="fas fa-clock highlight me-2"></i><strong>Suất:</strong> {{ date('H:i', strtotime($showtime->start_time)) }}
                        </div>
                        <div class="col-12 sub-box">
                            <i class="fas fa-chair highlight me-2"></i><strong>Ghế:</strong> {{ implode(', ', $selectedSeats) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COMBO -->
        <div class="info-box mb-5">
            <h4 class="text-warning mb-4"><i class="fas fa-gift me-2"></i>Combo Bắp Nước</h4>
            <div class="row g-3">
                @php $totalCombo = 0; @endphp
                @forelse ($selectedComboDetails as $combo)
                    @php 
                        $total = $combo['total_price'];
                        $totalCombo += $total;
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="sub-box h-100">
                            <strong>{{ $combo['combo']->name }}</strong><br>
                            SL: {{ $combo['quantity'] }}<br>
                            <span class="highlight">{{ number_format($total, 0, ',', '.') }}đ</span>
                        </div>
                    </div>
                @empty
                    <p class="text-white">Không chọn combo nào.</p>
                @endforelse
            </div>
            @if ($totalCombo > 0)
                <hr class="border-warning mt-4">
                <p class="fs-5"><strong>Tổng cộng combo:</strong> <span class="text-warning">{{ number_format($totalCombo, 0, ',', '.') }}đ</span></p>
            @endif
        </div>

        <!-- TỔNG TIỀN -->
        <div class="info-box mb-5">
            <h4 class="text-warning mb-4"><i class="fas fa-money-bill-wave me-2"></i>Tổng Thanh Toán</h4>
            @php $totalSeats = $totalAmount - $totalCombo; @endphp
            <div class="fs-5">
              <p><strong>Tiền vé:</strong> {{ number_format($totalSeats, 0, ',', '.') }}đ</p>
              <p><strong>Combo:</strong> {{ number_format($totalCombo, 0, ',', '.') }}đ</p>
              <hr class="border-warning">
              <p class="fs-4"><strong class="text-warning">Tổng cộng:</strong> <span class="text-warning">{{ number_format($totalAmount, 0, ',', '.') }}đ</span></p>
            </div>
        </div>
      
          <!-- Form xác nhận thanh toán -->
        <div class="info-box mb-4">
            <h4 class="text-warning mb-4"><i class="fas fa-credit-card me-2"></i>Chọn Phương Thức Thanh Toán</h4>
            <form method="POST" action="{{ route('payment.payos') }}">
                @csrf
                <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                <input type="hidden" name="theater_id" value="{{ $theater->id }}">
                <input type="hidden" name="date" value="{{ $date }}">
                <input type="hidden" name="showtime_id" value="{{ $showtime->id }}">
                <input type="hidden" name="total_amount" value="{{ $totalAmount }}">
            
                {{-- Truyền danh sách ghế --}}
                @foreach ($selectedSeats as $seat)
                    <input type="hidden" name="seats[]" value="{{ $seat }}">
                @endforeach
            
                {{-- Truyền combo nếu có --}}
                @foreach ($selectedComboDetails as $combo)
                    <input type="hidden" name="combo[{{ $combo['combo']->id }}]" value="{{ $combo['quantity'] }}">
                @endforeach
            
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="payment_method" id="payos" value="payos" checked>
                    <label class="form-check-label" for="payos">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/e7/PayOS_Logo_2024.png" width="80" alt="PayOS">
                        Thanh toán qua PayOS
                    </label>
                </div>
            
                <button type="submit" class="btn btn-warning btn-lg px-5 py-3 fw-bold shadow">
                    Tiến Hành Thanh Toán
                </button>
            </form>
            
        </div>
    </div>
</main>

@include('layouts.footer')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>