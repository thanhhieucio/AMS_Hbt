@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.domain_title') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <x-container class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
        <x-form :route="route('settings.domain.save')">
            <x-box>
                <x-slot:header>
                    <x-icon type="globe-us"/> {{ trans('admin/settings/general.domain_title') }}
                </x-slot:header>

                {{-- Single demo-mode banner. Renders nothing outside demo mode. --}}
                <x-demo-callout />

                <div class="col-md-12">

                    <x-callout type="warning" role="status">
                        {!! trans('admin/settings/general.domain_https_warning') !!}
                    </x-callout>

                    {{-- Read-only display of the currently effective APP_URL,
                         computed from config — not a form input, so hand-rolled
                         rather than routed through <x-form.row>. --}}
                    <div class="form-group">
                        <div class="col-md-3 control-label">
                            <strong>{{ trans('admin/settings/general.domain_current_effective_url') }}</strong>
                        </div>
                        <div class="col-md-8">
                            <p class="form-control-static"><code>{{ config('app.url') }}</code></p>
                        </div>
                    </div>

                    {{-- APP_URL --}}
                    <x-form.row
                        :label="trans('admin/settings/general.domain_app_url')"
                        name="app_url"
                        input_div_class="col-md-8"
                    >
                        <x-slot:input>
                            <x-input.text
                                name="app_url"
                                :value="old('app_url', config('app.url'))"
                                placeholder="https://portal.hsb.edu.vn"
                                :disabled="config('app.lock_passwords') === true"
                            />
                            <p class="help-block">{{ trans('admin/settings/general.domain_app_url_help') }}</p>
                        </x-slot:input>
                    </x-form.row>

                </div>

                {{-- Explicit footer preserves the demo-mode-disabled save state
                     that the default x-box.footer doesn't offer. --}}
                <x-slot:customfooter>
                    <div class="box-footer">
                        <div class="text-left col-md-6">
                            <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                        </div>
                        <div class="text-right col-md-6">
                            <x-button.submit class="btn-success" :disabled="config('app.lock_passwords') === true" />
                        </div>
                    </div>
                </x-slot:customfooter>
            </x-box>
        </x-form>
    </x-container>

@stop
