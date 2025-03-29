<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh Toán PayOS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #fff0f5, #ffe0ec);
            font-family: 'Segoe UI', sans-serif;
        }
        .payment-box {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .qr-box iframe {
            width: 100%;
            height: 500px;
            border: 6px solid #e83e8c;
            border-radius: 1rem;
        }
        .timer {
            font-size: 1.25rem;
            background: #ffe0ec;
            color: #e83e8c;
            border-radius: 10px;
            padding: 10px 20px;
            display: inline-block;
        }
        .btn-back {
            margin-top: 1rem;
            text-decoration: underline;
            color: #e83e8c;
            background: transparent;
            border: none;
        }
        .qr-heading {
            color: #e83e8c;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center g-4">
        <!-- Cột trái: Thông tin đơn hàng -->
        <div class="col-md-5">
            <div class="payment-box">
                <h4 class="fw-bold mb-4">Thông tin đơn hàng</h4>
                <p><strong>Nhà cung cấp:</strong> <br>
                    <img src="{{ asset('images/banner/logo.jpg') }}" alt="Logo" width="140">
                </p>
                <p><strong>Mã đơn hàng:</strong> <br>{{ $orderId }}</p>
                <p><strong>Mô tả:</strong> <br>Thanh toán đơn hàng đặt vé xem phim</p>
                <p><strong>Số tiền:</strong> <br class="d-md-none">
                    <span class="text-danger fs-4 fw-bold">{{ number_format($amount, 0, ',', '.') }}đ</span>
                </p>
                <div class="text-center mt-4">
                    <div id="countdown" class="timer">Đơn hàng sẽ hết hạn sau: <span id="minutes">10</span>:<span id="seconds">00</span></div>
                </div>
                <div class="text-center">
                    <button onclick="history.back()" class="btn-back">⬅ Quay về</button>
                </div>
            </div>
        </div>

        <!-- Cột phải: QR code (iframe PayOS) -->
        <div class="col-md-6 text-center">
            <div class="payment-box qr-box">
                <h4 class="qr-heading fw-bold mb-4">Quét mã QR để thanh toán</h4>
                <iframe src="{{ $checkoutUrl }}" frameborder="0"></iframe>
                <p class="text-muted mt-3">Sử dụng App PayOS hoặc Momo, ViettelPay... để quét mã</p>
            </div>
        </div>
    </div>
</div>

<script>
    let minutes = 10;
    let seconds = 0;
    const minutesEl = document.getElementById('minutes');
    const secondsEl = document.getElementById('seconds');

    const countdown = setInterval(() => {
        if (seconds === 0) {
            if (minutes === 0) {
                clearInterval(countdown);
                secondsEl.innerText = '00';
                alert('Đơn hàng đã hết hạn!');
                return;
            }
            minutes--;
            seconds = 59;
        } else {
            seconds--;
        }

        minutesEl.innerText = String(minutes).padStart(2, '0');
        secondsEl.innerText = String(seconds).padStart(2, '0');
    }, 1000);
</script>
</body>
</html>
