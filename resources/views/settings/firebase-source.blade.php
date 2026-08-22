@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.firebase_source_title') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <x-container class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
        <x-form :route="route('settings.firebase_source.save')">
            <x-box>
                <x-slot:header>
                    <i class="fas fa-fire" aria-hidden="true"></i> {{ trans('admin/settings/general.firebase_source_title') }}
                </x-slot:header>

                <div class="col-md-12">

                    <p>{{ trans('admin/settings/general.firebase_source_help') }} Bước này chỉ lưu thông số kết nối &mdash; chưa lấy dữ liệu, chưa gọi Firestore.</p>

                    @if ($firebaseSource['service_account_client_email'] ?? false)
                        <div class="alert alert-info">
                            Đã cấu hình Service Account: <code>{{ $firebaseSource['service_account_client_email'] }}</code>
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="firebase_project_id">Firebase Project ID</label>
                        <div class="col-md-8">
                            <input class="form-control" type="text" name="firebase_project_id" id="firebase_project_id" required
                                   value="{{ old('firebase_project_id', $firebaseSource['firebase_project_id'] ?? '') }}">
                            <p class="help-block">ID dự án Firebase của phần mềm nguồn (vd: <code>hbt-software</code>).</p>
                            <x-form.error name="firebase_project_id" :bag="'firebase_source'" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="firestore_students_collection">Collection sinh viên</label>
                        <div class="col-md-8">
                            <input class="form-control" type="text" name="firestore_students_collection" id="firestore_students_collection" required
                                   value="{{ old('firestore_students_collection', $firebaseSource['firestore_students_collection'] ?? '') }}">
                            <p class="help-block">Tên collection Firestore chứa danh sách sinh viên cần lấy (vd: <code>students</code>).</p>
                            <x-form.error name="firestore_students_collection" :bag="'firebase_source'" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="service_account_base64">Service Account (base64)</label>
                        <div class="col-md-8">
                            <textarea class="form-control" name="service_account_base64" id="service_account_base64" rows="4"
                                      placeholder="{{ ($firebaseSource['service_account_client_email'] ?? false) ? 'Để trống nếu giữ nguyên khóa hiện tại' : 'Dán nội dung base64 của file JSON Service Account' }}"></textarea>
                            <p class="help-block">Nội dung file JSON Service Account (Firebase Console &gt; Project settings &gt; Service accounts) đã mã hoá base64. Không dán JSON thô.</p>
                            <x-form.error name="service_account_base64" :bag="'firebase_source'" />
                        </div>
                    </div>

                </div>

                <x-slot:customfooter>
                    <div class="box-footer">
                        <div class="text-left col-md-6">
                            <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                        </div>
                        <div class="text-right col-md-6">
                            <x-button.submit class="btn-success" />
                        </div>
                    </div>
                </x-slot:customfooter>
            </x-box>
        </x-form>
    </x-container>

@stop
