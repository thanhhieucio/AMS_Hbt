@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.portal_title') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <x-container class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
        <x-form :route="route('settings.portal.save')">
            <x-box>
                <x-slot:header>
                    <x-icon type="dashboard"/> {{ trans('admin/settings/general.portal_title') }}
                </x-slot:header>

                {{-- Single demo-mode banner. Renders nothing outside demo mode. --}}
                <x-demo-callout />

                <div class="col-md-12">

                    {{-- Label shown on the HSB-IT asset management tile on the
                         portal hub page. Falls back to the global site name
                         when left blank. --}}
                    <x-form.row
                        :label="trans('admin/settings/general.portal_hsbit_label')"
                        name="portal_hsbit_label"
                        input_div_class="col-md-8"
                    >
                        <x-slot:input>
                            <x-input.text
                                name="portal_hsbit_label"
                                :value="old('portal_hsbit_label', $setting->portal_hsbit_label)"
                                :placeholder="$setting->site_name"
                                :disabled="config('app.lock_passwords') === true"
                            />
                            <p class="help-block">{{ trans('admin/settings/general.portal_hsbit_label_help') }}</p>
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
