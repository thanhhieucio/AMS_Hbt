@extends('layouts/setup')
@section('title')
Kiểm tra hệ thống ::
@parent
@stop

@section('content')

<h4>Trước khi cài đặt, HSB-IT sẽ kiểm tra nhanh cấu hình máy chủ.</h4>

<table class="table">
  <thead>
    <tr>
      <th scope="col" class="col-lg-2">Cấu hình</th>
      <th scope="col" class="col-lg-1">Hợp lệ</th>
      <th scope="col" class="col-lg-9">Ghi chú</th>
    </tr>
  </thead>
  <tbody>
  <tr {!! ($start_settings['php_version_min']) ? ' class="success"' : ' class="danger"' !!}>
      <td>PHP</td>
      <td>@if ($start_settings['php_version_min'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if ($start_settings['php_version_min'])Đạt yêu cầu.@else Chưa đạt yêu cầu.@endif Bạn đang chạy PHP {{ PHP_VERSION }}. Yêu cầu tối thiểu {{ config('app.min_php') }}.</td>
  </tr>

    <tr {!! ($start_settings['url_valid']) ? ' class="success"' : ' class="danger"' !!}>
      <td>URL</td>
      <td>@if ($start_settings['url_valid'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if ($start_settings['url_valid'])URL đang cấu hình đúng.@else HSB-IT đang nhận URL là {{ $start_settings['url_config'] }}, nhưng URL thực tế là {{ $start_settings['real_url'] }}. Vui lòng cập nhật <code>APP_URL</code> trong file <code>.env</code>. @endif</td>
    </tr>

    <tr {!! ($start_settings['db_conn']===true) ? ' class="success"' : ' class="danger"' !!}>
      <td>Cơ sở dữ liệu</td>
      <td>@if ($start_settings['db_conn']===true)<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if ($start_settings['db_conn']===true)Đã kết nối tới <code>{{ $start_settings['db_name'] }}</code>.@else Không kết nối được cơ sở dữ liệu. Vui lòng kiểm tra cấu hình database trong <code>.env</code>. Lỗi trả về: <code>{{ $start_settings['db_error'] }}</code> @endif</td>
    </tr>

    <tr {!! (!$start_settings['env_exposed']) ? ' class="success"' : ' class="danger"' !!}>
      <td>File cấu hình</td>
      <td>@if (!$start_settings['env_exposed'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if (!$start_settings['env_exposed'])File <code>.env</code> không bị public ra ngoài. Bạn vẫn nên kiểm tra lại bằng trình duyệt: <a href="../../.env">kiểm tra .env</a>.@else Không xác định được file <code>.env</code> có bị public hay không. Hãy kiểm tra thủ công vì file này có thể chứa thông tin nhạy cảm. @endif</td>
    </tr>

    <tr {!! ($start_settings['prod']) ? ' class="success"' : ' class="warning"' !!}>
      <td>Môi trường</td>
      <td>@if ($start_settings['prod'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if ($start_settings['prod'])Ứng dụng đang ở chế độ production.@else Ứng dụng đang ở chế độ <code>{{ $start_settings['env'] }}</code>. Nếu không phải môi trường dev, hãy đổi <code>APP_ENV</code> trong <code>.env</code> thành <code>production</code>. @endif</td>
    </tr>

    <tr {!! (!$start_settings['owner_is_admin']) ? ' class="success"' : ' class="danger"' !!}>
      <td>Chủ sở hữu file</td>
      <td>@if (!$start_settings['owner_is_admin'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if (!$start_settings['owner_is_admin'])File ứng dụng thuộc user <code>{{ $start_settings['owner'] }}</code>.@else File đang thuộc <code>{{ $start_settings['owner'] }}</code>, có vẻ là tài khoản root/admin. Không nên chạy web bằng quyền cao. @endif</td>
    </tr>

    <tr {!! (!$start_settings['writable']) ? ' class="danger"' : ' class="success"' !!}>
      <td>Quyền ghi</td>
      <td>@if ($start_settings['writable'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if ($start_settings['writable'])Thư mục storage có quyền ghi.@else Thư mục <code>{{ storage_path() }}</code> hoặc thư mục con không ghi được bởi web server. @endif</td>
    </tr>

    <tr {!! ($start_settings['debug_exposed']) ? ' class="danger"' : ' class="success"' !!}>
      <td>Debug</td>
      <td>@if (!$start_settings['debug_exposed'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if (!$start_settings['debug_exposed'])Debug đang tắt hoặc không public ở production.@else <p>Nên tắt debug trước khi chạy thật. Cập nhật <code>APP_DEBUG</code> trong <code>.env</code>.</p> @endif</td>
    </tr>

    <tr {!! ($start_settings['gd']) ? ' class="success"' : ' class="warning"' !!}>
      <td>Thư viện ảnh</td>
      <td>@if ($start_settings['gd'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-warning"></i>@endif</td>
      <td>@if ($start_settings['gd'])<p>GD đã được cài đặt.</p>@else <p>Chưa có GD. Hệ thống vẫn chạy, nhưng sẽ bị hạn chế khi tạo tem nhãn hoặc upload ảnh.</p> @endif</td>
    </tr>

    <tr id="mailtestrow" class="info">
      <td>Email</td>
      <td><span id="mailtesticon" role="status" aria-live="polite" aria-atomic="true"></span></td>
      <td>
        <p>Hệ thống sẽ gửi email test đến {{ config('mail.from.address') }}.</p>
        <a class="btn btn-default btn-sm pull-left" id="mailtest" style="margin-right: 10px;">Gửi test</a>
        <div id="mailteststatus-text" class="text-danger" role="status" aria-live="polite" aria-atomic="true"></div>
      </td>
    </tr>
  </tbody>
</table>

@php
    $db_config_open = (! $start_settings['db_conn']) || $errors->database->any();
    $db_current_driver = old('db_connection', config('database.default'));
@endphp

<div class="panel panel-default" style="margin-top: 20px;">
  <div class="panel-heading">
    <h4 style="margin: 0;">
      <a data-toggle="collapse" href="#dbConfigForm" role="button" aria-expanded="{{ $db_config_open ? 'true' : 'false' }}" aria-controls="dbConfigForm">
        <i class="fa-solid fa-database"></i> Cấu hình kết nối cơ sở dữ liệu
      </a>
    </h4>
  </div>
  <div id="dbConfigForm" class="panel-collapse collapse {{ $db_config_open ? 'in' : '' }}">
    <div class="panel-body">

      <p>Hỗ trợ MySQL/MariaDB và PostgreSQL &mdash; kể cả <strong>Cloud SQL for PostgreSQL</strong>. Cloud SQL nói chuẩn giao thức PostgreSQL nên chọn <code>PostgreSQL</code> bên dưới là đúng.</p>

      @if ($errors->database->has('db_connection_test'))
        <div class="alert alert-danger">
          Không kết nối được cơ sở dữ liệu với thông tin vừa nhập: <code>{{ $errors->database->first('db_connection_test') }}</code>
        </div>
      @endif

      <form action="{{ route('setup.database.save') }}" method="POST">
        @csrf

        <div class="row">
          <div class="form-group col-lg-4">
            <label for="db_connection">Loại cơ sở dữ liệu</label>
            <select class="form-control" name="db_connection" id="db_connection">
              <option value="mysql" {{ $db_current_driver === 'mysql' ? 'selected' : '' }}>MySQL / MariaDB</option>
              <option value="pgsql" {{ $db_current_driver === 'pgsql' ? 'selected' : '' }}>PostgreSQL (Cloud SQL for PostgreSQL)</option>
            </select>
            <x-form.error name="db_connection" :bag="'database'" />
          </div>

          <div class="form-group col-lg-4">
            <label for="db_host">Host</label>
            <input class="form-control" type="text" name="db_host" id="db_host" placeholder="127.0.0.1 hoặc IP Cloud SQL" required
                   value="{{ old('db_host', config('database.connections.'.config('database.default').'.host')) }}">
            <x-form.error name="db_host" :bag="'database'" />
          </div>

          <div class="form-group col-lg-4">
            <label for="db_port">Port</label>
            <input class="form-control" type="number" name="db_port" id="db_port" min="1" max="65535" required
                   value="{{ old('db_port', config('database.connections.'.config('database.default').'.port')) }}">
            <x-form.error name="db_port" :bag="'database'" />
          </div>
        </div>

        <div class="row">
          <div class="form-group col-lg-4">
            <label for="db_database">Tên cơ sở dữ liệu</label>
            <input class="form-control" type="text" name="db_database" id="db_database" required
                   value="{{ old('db_database', config('database.connections.'.config('database.default').'.database')) }}">
            <x-form.error name="db_database" :bag="'database'" />
          </div>

          <div class="form-group col-lg-4">
            <label for="db_username">Tên đăng nhập</label>
            <input class="form-control" type="text" name="db_username" id="db_username" required
                   value="{{ old('db_username', config('database.connections.'.config('database.default').'.username')) }}">
            <x-form.error name="db_username" :bag="'database'" />
          </div>

          <div class="form-group col-lg-4">
            <label for="db_password">Mật khẩu</label>
            <input class="form-control" type="password" name="db_password" id="db_password" autocomplete="new-password"
                   placeholder="Để trống nếu giữ nguyên mật khẩu hiện tại">
            <x-form.error name="db_password" :bag="'database'" />
          </div>
        </div>

        <div class="row" id="db_sslmode_row" style="{{ $db_current_driver === 'pgsql' ? '' : 'display:none;' }}">
          <div class="form-group col-lg-12">
            <label class="checkbox-inline">
              <input type="checkbox" name="db_sslmode" value="1" {{ old('db_sslmode') ? 'checked' : '' }}>
              Bắt buộc SSL &mdash; bật nếu kết nối Cloud SQL qua địa chỉ IP công khai; tắt nếu kết nối qua Cloud SQL Auth Proxy trên localhost.
            </label>
          </div>
        </div>

        <button type="submit" class="btn btn-default">
          <i class="fa-solid fa-plug"></i> Kiểm tra &amp; lưu cấu hình cơ sở dữ liệu
        </button>
      </form>
    </div>
  </div>
</div>

@stop

@section('button')
  <form action="{{ route('setup.migrate') }}" method="POST">
      @csrf
    <button class="btn btn-primary">
        {{ trans('general.setup_next') }}: {{ trans('general.setup_create_database') }}
        <i class="fa-solid fa-angles-right"></i>
    </button>
  </form>

@parent
@stop

@section('moar_scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $("#db_connection").on('change', function () {
            var driver = $(this).val();
            var $port = $("#db_port");

            if (driver === 'pgsql') {
                $("#db_sslmode_row").show();
                if ($port.val() === '3306' || $port.val() === '') {
                    $port.val('5432');
                }
            } else {
                $("#db_sslmode_row").hide();
                if ($port.val() === '5432' || $port.val() === '') {
                    $port.val('3306');
                }
            }
        });

        $("#mailtest").click(function(){
            $("#mailtestrow").removeClass('success').removeClass('danger').removeClass('warning').addClass('info');
            $("#mailtesticon").html('<i class="fas fa-spinner fa-spin text-info"></i>');
            $("#mailteststatus-text").removeClass('text-success').addClass('text-danger').html('Đang gửi email test...');
            $.ajax({
                url: "{{ route('setup.mailtest') }}",
                success: function (result) {
                    if (result.status == 'success') {
                        $("#mailtestrow").removeClass('info danger warning').addClass('success');
                        $("#mailtesticon").html('<i class="fas fa-check preflight-success"></i>');
                        $("#mailteststatus-text").removeClass('text-danger').addClass('text-success').html('Đã gửi email đến {{ config('mail.from.address') }}.');
                    } else {
                        $("#mailtestrow").removeClass('success info warning').addClass('danger');
                        $("#mailtesticon").html('<i class="fas fa-times preflight-error"></i>');
                        $("#mailteststatus-text").removeClass('text-success').addClass('text-danger').html('Gửi email thất bại. Hãy kiểm tra cấu hình mail trong .env.');
                    }
                },
                error: function () {
                    $("#mailtestrow").removeClass('success info warning').addClass('danger');
                    $("#mailtesticon").html('<i class="fas fa-exclamation-triangle text-danger"></i>');
                    $("#mailteststatus-text").removeClass('text-success').addClass('text-danger').html('Không gửi được email.');
                }
            });
        });
 });
</script>
@stop