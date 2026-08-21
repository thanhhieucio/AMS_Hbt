@extends('layouts/setup')
@section('title')
Kiem tra he thong ::
@parent
@stop

@section('content')

<h4>Truoc khi cai dat, HSB-IT se kiem tra nhanh cau hinh may chu.</h4>

<table class="table">
  <thead>
    <tr>
      <th scope="col" class="col-lg-2">Cau hinh</th>
      <th scope="col" class="col-lg-1">Hop le</th>
      <th scope="col" class="col-lg-9">Ghi chu</th>
    </tr>
  </thead>
  <tbody>
  <tr {!! ($start_settings['php_version_min']) ? ' class="success"' : ' class="danger"' !!}>
      <td>PHP</td>
      <td>@if ($start_settings['php_version_min'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if ($start_settings['php_version_min'])Dat yeu cau.@else Chua dat yeu cau.@endif Ban dang chay PHP {{ PHP_VERSION }}. Yeu cau toi thieu {{ config('app.min_php') }}.</td>
  </tr>

    <tr {!! ($start_settings['url_valid']) ? ' class="success"' : ' class="danger"' !!}>
      <td>URL</td>
      <td>@if ($start_settings['url_valid'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if ($start_settings['url_valid'])URL dang cau hinh dung.@else HSB-IT dang nhan URL la {{ $start_settings['url_config'] }}, nhung URL thuc te la {{ $start_settings['real_url'] }}. Vui long cap nhat <code>APP_URL</code> trong file <code>.env</code>. @endif</td>
    </tr>

    <tr {!! ($start_settings['db_conn']===true) ? ' class="success"' : ' class="danger"' !!}>
      <td>Database</td>
      <td>@if ($start_settings['db_conn']===true)<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if ($start_settings['db_conn']===true)Da ket noi toi <code>{{ $start_settings['db_name'] }}</code>.@else Khong ket noi duoc database. Vui long kiem tra cau hinh database trong <code>.env</code>. Loi tra ve: <code>{{ $start_settings['db_error'] }}</code> @endif</td>
    </tr>

    <tr {!! (!$start_settings['env_exposed']) ? ' class="success"' : ' class="danger"' !!}>
      <td>File cau hinh</td>
      <td>@if (!$start_settings['env_exposed'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if (!$start_settings['env_exposed'])File <code>.env</code> khong bi public ra ngoai. Ban van nen kiem tra lai bang trinh duyet: <a href="../../.env">kiem tra .env</a>.@else Khong xac dinh duoc file <code>.env</code> co bi public hay khong. Hay kiem tra thu cong vi file nay co the chua thong tin nhay cam. @endif</td>
    </tr>

    <tr {!! ($start_settings['prod']) ? ' class="success"' : ' class="warning"' !!}>
      <td>Moi truong</td>
      <td>@if ($start_settings['prod'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if ($start_settings['prod'])Ung dung dang o che do production.@else Ung dung dang o che do <code>{{ $start_settings['env'] }}</code>. Neu khong phai moi truong dev, hay doi <code>APP_ENV</code> trong <code>.env</code> thanh <code>production</code>. @endif</td>
    </tr>

    <tr {!! (!$start_settings['owner_is_admin']) ? ' class="success"' : ' class="danger"' !!}>
      <td>Chu so huu file</td>
      <td>@if (!$start_settings['owner_is_admin'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if (!$start_settings['owner_is_admin'])File ung dung thuoc user <code>{{ $start_settings['owner'] }}</code>.@else File dang thuoc <code>{{ $start_settings['owner'] }}</code>, co ve la tai khoan root/admin. Khong nen chay web bang quyen cao. @endif</td>
    </tr>

    <tr {!! (!$start_settings['writable']) ? ' class="danger"' : ' class="success"' !!}>
      <td>Quyen ghi</td>
      <td>@if ($start_settings['writable'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if ($start_settings['writable'])Thu muc storage co quyen ghi.@else Thu muc <code>{{ storage_path() }}</code> hoac thu muc con khong ghi duoc boi web server. @endif</td>
    </tr>

    <tr {!! ($start_settings['debug_exposed']) ? ' class="danger"' : ' class="success"' !!}>
      <td>Debug</td>
      <td>@if (!$start_settings['debug_exposed'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-error"></i>@endif</td>
      <td>@if (!$start_settings['debug_exposed'])Debug dang tat hoac khong public o production.@else <p>Nen tat debug truoc khi chay that. Cap nhat <code>APP_DEBUG</code> trong <code>.env</code>.</p> @endif</td>
    </tr>

    <tr {!! ($start_settings['gd']) ? ' class="success"' : ' class="warning"' !!}>
      <td>Thu vien anh</td>
      <td>@if ($start_settings['gd'])<i class="fas fa-check preflight-success"></i>@else<i class="fas fa-times preflight-warning"></i>@endif</td>
      <td>@if ($start_settings['gd'])<p>GD da duoc cai dat.</p>@else <p>Chua co GD. He thong van chay, nhung se bi han che khi tao tem nhan hoac upload anh.</p> @endif</td>
    </tr>

    <tr id="mailtestrow" class="info">
      <td>Email</td>
      <td><span id="mailtesticon" role="status" aria-live="polite" aria-atomic="true"></span></td>
      <td>
        <p>He thong se gui email test den {{ config('mail.from.address') }}.</p>
        <a class="btn btn-default btn-sm pull-left" id="mailtest" style="margin-right: 10px;">Gui test</a>
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
        <i class="fa-solid fa-database"></i> Cấu hình kết nối Database
      </a>
    </h4>
  </div>
  <div id="dbConfigForm" class="panel-collapse collapse {{ $db_config_open ? 'in' : '' }}">
    <div class="panel-body">

      <p>Hỗ trợ MySQL/MariaDB và PostgreSQL &mdash; kể cả <strong>Cloud SQL for PostgreSQL</strong> (Cloud SQL nói chuẩn giao thức PostgreSQL nên chọn <code>PostgreSQL</code> bên dưới là đúng).</p>

      @if ($errors->database->has('db_connection_test'))
        <div class="alert alert-danger">
          Không kết nối được database với thông tin vừa nhập: <code>{{ $errors->database->first('db_connection_test') }}</code>
        </div>
      @endif

      <form action="{{ route('setup.database.save') }}" method="POST">
        @csrf

        <div class="row">
          <div class="form-group col-lg-4">
            <label for="db_connection">Loại database</label>
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
            <label for="db_database">Tên database</label>
            <input class="form-control" type="text" name="db_database" id="db_database" required
                   value="{{ old('db_database', config('database.connections.'.config('database.default').'.database')) }}">
            <x-form.error name="db_database" :bag="'database'" />
          </div>

          <div class="form-group col-lg-4">
            <label for="db_username">Username</label>
            <input class="form-control" type="text" name="db_username" id="db_username" required
                   value="{{ old('db_username', config('database.connections.'.config('database.default').'.username')) }}">
            <x-form.error name="db_username" :bag="'database'" />
          </div>

          <div class="form-group col-lg-4">
            <label for="db_password">Password</label>
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
          <i class="fa-solid fa-plug"></i> Kiểm tra &amp; lưu cấu hình database
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
            $("#mailteststatus-text").removeClass('text-success').addClass('text-danger').html('Dang gui email test...');
            $.ajax({
                url: "{{ route('setup.mailtest') }}",
                success: function (result) {
                    if (result.status == 'success') {
                        $("#mailtestrow").removeClass('info danger warning').addClass('success');
                        $("#mailtesticon").html('<i class="fas fa-check preflight-success"></i>');
                        $("#mailteststatus-text").removeClass('text-danger').addClass('text-success').html('Da gui email den {{ config('mail.from.address') }}.');
                    } else {
                        $("#mailtestrow").removeClass('success info warning').addClass('danger');
                        $("#mailtesticon").html('<i class="fas fa-times preflight-error"></i>');
                        $("#mailteststatus-text").removeClass('text-success').addClass('text-danger').html('Gui email that bai. Hay kiem tra cau hinh mail trong .env.');
                    }
                },
                error: function () {
                    $("#mailtestrow").removeClass('success info warning').addClass('danger');
                    $("#mailtesticon").html('<i class="fas fa-exclamation-triangle text-danger"></i>');
                    $("#mailteststatus-text").removeClass('text-success').addClass('text-danger').html('Khong gui duoc email.');
                }
            });
        });
 });
</script>
@stop