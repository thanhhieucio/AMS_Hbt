@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/components/general.checkout') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

<x-container columns="2">
    <x-page-column class="col-md-7">

        <x-form route="{{ route('components.checkout.store', $hsb_component->id) }}" id="checkout_form">

            <x-box header="{{ $hsb_component->name }} ({{ $hsb_component->numRemaining() }} {{ trans('admin/components/general.remaining') }})">

            @if ($hsb_component->company)
                <x-form.static :label="trans('general.company')">{!! $hsb_component->company->present()->formattedNameLink !!}</x-form.static>
            @endif

            @if ($hsb_component->category)
                <x-form.static :label="trans('general.category')">{!! $hsb_component->category->present()->formattedNameLink !!}</x-form.static>
            @endif

            @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.select_asset'), 'fieldname' => 'asset_id', 'company_id' => $hsb_component->company_id, 'required' => 'true', 'value' => old('asset_id')])

            <x-input.quantity
                name="assigned_qty"
                :value="1"
                :min="1"
                :max="$hsb_component->numRemaining()"
                :label="trans('general.qty')"
            />

            @if ($hsb_component->requireAcceptance() || $hsb_component->getEula() || ($hsbSettings->webhook_endpoint != ''))
                <div class="form-group notification-callout">
                    <div class="col-md-8 col-md-offset-3">
                        <x-callout type="info" role="status">
                            @if ($hsb_component->category->require_acceptance == '1')
                                <i class="far fa-envelope fa-fw" aria-hidden="true"></i>
                                {{ trans('admin/categories/general.required_acceptance') }}<br>
                            @endif
                            @if ($hsb_component->getEula())
                                <i class="far fa-envelope fa-fw" aria-hidden="true"></i>
                                {{ trans('admin/categories/general.required_eula') }}<br>
                            @endif
                            @if ($hsbSettings->webhook_endpoint != '')
                                <i class="fab fa-slack fa-fw" aria-hidden="true"></i>
                                {{ trans('general.webhook_msg_note') }}
                            @endif
                        </x-callout>
                    </div>
                </div>
            @endif

            <x-form.row
                :label="trans('admin/hardware/form.notes')"
                :item="$hsb_component"
                name="note"
                type="textarea"
            />

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="components.index"
                    :button_label="trans('general.checkout')"
                    :options="[
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.components')]),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.component')]),
                        'target' => trans('admin/hardware/form.redirect_to_checked_out_to'),
                    ]"
                />
            </x-slot:customfooter>

            </x-box>

        </x-form>

    </x-page-column>

    <x-page-column class="col-md-5">
        <livewire:checkout-target-panel type="components" defaultTargetType="asset" />
    </x-page-column>

</x-container>

@stop
