@extends('layouts/setup')
@section('title')
{{ trans('general.create_admin_user') }}
@parent
@stop

@section('content')

    <style>
        .well-warning {
            color: #8a6d3b;
            background-color: #fcf8e3;
            border-color: #faebcc;
        }
    </style>
    <div class="col-md-12">
        <p>
            Cài đặt HSB-IT đã hoàn tất. Bạn có thể <strong><a href="{{ config('app.url') }}">vào dashboard</a></strong> để bắt đầu quản lý tài sản.
        </p>
        <div class="well well-sm">
            <div class="row">
                <div class="col-md-6">
                    <ul>
                        <li><i class="fa-solid fa-list-check fa-fw"></i> Tạo phòng ban, địa điểm và người dùng.</li>
                        <li><i class="fa-solid fa-tags fa-fw"></i> Cấu hình mã tài sản, tem nhãn và mã QR.</li>
                        <li><i class="fa-solid fa-file-import fa-fw"></i> Import tài sản từ CSV nếu đã có dữ liệu.</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul>
                        <li><i class="fa-solid fa-envelope fa-fw"></i> Cấu hình SMTP thật nếu cần gửi email bàn giao.</li>
                        <li><i class="fa-solid fa-shield-halved fa-fw"></i> Kiểm tra quyền admin và phân quyền người dùng.</li>
                        <li><i class="fa-solid fa-database fa-fw"></i> Thiết lập lịch backup định kỳ.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="well well-sm well-warning">
            <p><x-icon type="tip" /> <strong>Lưu ý khi đồng bộ user bằng SCIM hoặc LDAP</strong></p>
            <p>Nếu dùng SCIM/LDAP, hãy thống nhất định dạng username giữa file CSV và dịch vụ danh bạ để tránh tạo trùng người dùng.</p>
        </div>
    </div>

@stop

@section('button')
    <a class="btn btn-primary" href="{{ config('app.url') }}">{{ trans('admin/settings/general.create_admin_redirect') }}
        <i class="fa-solid fa-angles-right"></i>
    </a>
    @parent
@stop

<script>
    var duration = 2000;
    var animationEnd = Date.now() + duration;
    var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };
    function randomInRange(min, max) { return Math.random() * (max - min) + min; }
    var interval = setInterval(function() {
        var timeLeft = animationEnd - Date.now();
        if (timeLeft <= 0) { return clearInterval(interval); }
        var particleCount = 50 * (timeLeft / duration);
        confetti({ ...defaults, particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } });
        confetti({ ...defaults, particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } });
    }, 250);
</script>