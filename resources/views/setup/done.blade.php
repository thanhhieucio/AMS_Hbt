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
            Cai dat HSB-IT da hoan tat. Ban co the <strong><a href="{{ config('app.url') }}">vao dashboard</a></strong> de bat dau quan ly tai san.
        </p>
        <div class="well well-sm">
            <div class="row">
                <div class="col-md-6">
                    <ul>
                        <li><i class="fa-solid fa-list-check fa-fw"></i> Tao phong ban, dia diem va nguoi dung.</li>
                        <li><i class="fa-solid fa-tags fa-fw"></i> Cau hinh ma tai san, tem nhan va ma QR.</li>
                        <li><i class="fa-solid fa-file-import fa-fw"></i> Import tai san tu CSV neu da co du lieu.</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul>
                        <li><i class="fa-solid fa-envelope fa-fw"></i> Cau hinh SMTP that neu can gui email ban giao.</li>
                        <li><i class="fa-solid fa-shield-halved fa-fw"></i> Kiem tra quyen admin va phan quyen nguoi dung.</li>
                        <li><i class="fa-solid fa-database fa-fw"></i> Thiet lap lich backup dinh ky.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="well well-sm well-warning">
            <p><x-icon type="tip" /> <strong>Luu y khi dong bo user bang SCIM hoac LDAP</strong></p>
            <p>Neu dung SCIM/LDAP, hay thong nhat dinh dang username giua file CSV va dich vu danh ba de tranh tao trung nguoi dung.</p>
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