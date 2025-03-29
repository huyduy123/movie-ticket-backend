<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Hệ Thống Bán Vé Xem Phim</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS Custom -->
    @vite(['resources/css/style.css'])
    @vite(['resources/css/footer.css'])
    @vite(['resources/css/header.css'])

   
  
</head>
<body>
    @include('layouts.header') <!-- Header cùng màu -->
<main class="main-container">
    <div class="container mt-4">
        <!-- Banner -->
        <div id="carouselBanner" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach ($banners as $index => $banner)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <a href="{{ url($banner->link) }}">
                            <img src="{{ url('images/banner/' . $banner->image) }}" class="d-block w-100 banner" alt="{{ $banner->title }}">
                        </a>
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselBanner" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselBanner" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <!-- Đặt vé nhanh -->
    <div class="container mt-3">
    <div class="quick-booking">
        <h4 class="title">🎟️ ĐẶT VÉ</h4>
        <form action="{{ route('booking.store') }}" method="POST">
            @csrf
    <div class="booking-options">
        <!-- Chọn rạp -->
        <select class="form-select" name="theater_id" id="theater" required>
            <option selected disabled value="">1. Chọn Rạp</option>
            @foreach ($theaters as $theater)
                <option value="{{ $theater->id }}">{{ $theater->name }}</option>
            @endforeach
        </select>

        <!-- Chọn phim -->
        <select class="form-select" name="movie_id" id="movie" required>
            <option selected disabled value="">2. Chọn Phim</option>
            @foreach ($movies as $movie)
                <option value="{{ $movie->id }}">{{ $movie->title }}</option>
            @endforeach
        </select>

        <!-- Chọn ngày -->
        <select class="form-select" name="date" id="date" required>
            <option selected disabled value="">3. Chọn Ngày</option>
            @foreach ($showtimes->unique('start_time') as $showtime)
                <option value="{{ date('Y-m-d', strtotime($showtime->start_time)) }}">
                    {{ date('d-m-Y', strtotime($showtime->start_time)) }}
                </option>
            @endforeach
        </select>

        <!-- Chọn suất chiếu -->
        <select class="form-select" name="showtime_id" id="showtime" required>
            <option selected disabled value="">4. Chọn Suất</option>
            @foreach ($showtimes as $showtime)
                <option value="{{ $showtime->id }}">{{ date('H:i', strtotime($showtime->start_time)) }}</option>
            @endforeach
        </select>

        <!-- Nút đặt vé -->
        <button type="submit" class="btn btn-purple">ĐẶT NGAY</button>
    </div>
</form>

    </div>
    </div>

    <div class="container mt-5">
        <h3 class="text-center text-white">PHIM ĐANG CHIẾU</h3>
    
        <div id="carouselMovies" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach ($movies->chunk(4) as $index => $movieChunk)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <div class="row row-cols-1 row-cols-md-4 g-4">
                            @foreach ($movieChunk as $movie)
                                <div class="col movie-item">
                                    <div class="card movie-card">
                                        <div class="movie-image-container">
                                            <img src="{{ url('images/' . $movie->image) }}" class="card-img-top" alt="{{ $movie->title }}">
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title">{{ $movie->title }}</h5>
                                            <p class="card-text">{{ Str::limit($movie->description, 100) }}</p>
                                            <div class="d-flex justify-content-between mt-auto gap-2">
                                                <a href="{{ $movie->trailer_url }}" target="_blank" class="btn btn-trailer">
                                                    🎬 Xem Trailer
                                                </a>
                                                <a href="{{ route('movies.show', ['id' => $movie->id]) }}" class="btn btn-ticket">ĐẶT VÉ</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
    
            <!-- Nút điều hướng giống banner -->
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselMovies" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselMovies" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
        <!-- Nút "Xem thêm" -->
        <div class="text-center mt-4">
            <button id="view-more" class="btn btn-light">Xem Thêm</button>
        </div>
    </div>
    
    <div class="container mt-5">
        <h3 class="text-center text-white">PHIM SẮP CHIẾU</h3>
        
        <div id="carouselUpcomingMovies" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach ($upcomingMovies->chunk(4) as $index => $movieChunk)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <div class="row row-cols-1 row-cols-md-4 g-4">
                            @foreach ($movieChunk as $movie)
                                <div class="col movie-item">
                                    <div class="card movie-card">
                                        <div class="movie-image-container">
                                            <img src="{{ url('images/' . $movie->image) }}" class="card-img-top" alt="{{ $movie->title }}">
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title">{{ $movie->title }}</h5>
                                            <p class="card-text">{{ Str::limit($movie->description, 100) }}</p>
                                            <div class="d-flex justify-content-between mt-auto gap-2">
                                                <a href="{{ $movie->trailer_url }}" target="_blank" class="btn btn-trailer">
                                                    🎬 Xem Trailer
                                                </a>
                                                <a href="#" class="btn btn-ticket">ĐẶT VÉ</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
    
            <!-- Nút điều hướng giống banner -->
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselUpcomingMovies" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselUpcomingMovies" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
        <!-- Nút "Xem thêm" -->
        <div class="text-center mt-4">
            <button id="view-more" class="btn btn-light">Xem Thêm</button>
        </div>
    </div>


    <!-- Phần Liên Hệ -->
    <div class="container contact-section">
        <div class="contact-container">
            <!-- Cột bên trái: Nút Facebook & Zalo -->
            <div class="contact-left">
                <h2><i class="fas fa-headset"></i> LIÊN HỆ VỚI CHÚNG TÔI</h2>
                <a href="https://www.facebook.com" class="btn btn-facebook">
                    <i class="fab fa-facebook"></i> FACEBOOK
                </a>
                <a href="https://zalo.me" class="btn btn-zalo">
                    <i class="fas fa-comments"></i> ZALO CHAT
                </a>
            </div>
    
            <!-- Cột bên phải: Form Liên Hệ -->
            <div class="contact-box">
                <h4><i class="fas fa-info-circle"></i> THÔNG TIN LIÊN HỆ</h4>
                <ul>
                    <li><i class="fas fa-envelope"></i> cskh@aomonglovecinema.com.vn</li>
                    <li><i class="fas fa-phone"></i> 1900.0085</li>
                    <li><i class="fas fa-map-marker-alt"></i> 290/34 dương bá trạc, quận 8, tp hcm</li>
                </ul>
                <form>
                    <input type="text" placeholder="Họ và tên" required>
                    <input type="email" placeholder="Điền email" required>
                    <textarea placeholder="Thông tin liên hệ hoặc phản ánh" required></textarea>
                    <button type="submit"><i class="fas fa-paper-plane"></i> GỬI NGAY</button>
                </form>
            </div>
        </div>
    </div>
</main>
    @include('layouts.footer') <!-- Footer chung màu -->
</body>



<!-- FontAwesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
@vite(['resources/js/home.js'])
<script>
    window.moviesData = @json($movies);
</script>
</html>
