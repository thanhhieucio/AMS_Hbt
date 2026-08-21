@extends('layouts/default')

{{-- Page title --}}
@section('title')
 {{ $component->name }}
 {{ trans('general.component') }}
@parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

@section('content')
<x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>

                    <x-tabs.nav-item
                            name="assigned"
                            icon_type="checkedout"
                            label="{{ trans('general.assigned') }}"
                            count="{{ $hsb_component->numCheckedOut() }}"
                    />

                    <x-tabs.files-tab :item="$hsb_component" count="{{ $hsb_component->uploads()->count() }}"/>
                    <x-tabs.history-tab count="{{ $hsb_component->history()->count() }}" :model="$hsb_component"/>
                    <x-tabs.upload-tab :item="$hsb_component"/>

                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <x-tabs.pane name="assigned">

                        <x-slot:table_header>
                            {{ trans('general.assigned') }}
                        </x-slot:table_header>

                        <x-table
                            :presenter="\App\Presenters\ComponentPresenter::checkedOut()"
                            :api_url="route('api.components.assets', $hsb_component)"
                        />

                    </x-tabs.pane>

                    <!-- start files tab pane -->
                    <x-tabs.pane name="files">
                        <x-table.files object_type="components" :object="$hsb_component"/>
                    </x-tabs.pane>

                    <!-- start history tab pane -->
                    <x-tabs.pane name="history">
                        <x-table.history :model="$hsb_component" :route="route('api.components.history', $hsb_component)"/>
                    </x-tabs.pane>

                </x-slot:tabpanes>
            </x-tabs>
        </x-page-column>
        <x-page-column class="col-md-3">

            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$hsb_component" img_path="{{ app('components_upload_url') }}" :qr_code_url="route('qr_code/common', ['object_type' => 'components', 'id' => $hsb_component->id])">

                    <x-slot:buttons>
                        <x-button.edit :item="$hsb_component" :route="route('components.edit', $hsb_component->id)"/>
                        <x-button.clone :item="$hsb_component" :route="route('components.clone.create', $hsb_component->id)"/>
                        <x-button.checkout :item="$hsb_component" :route="route('components.checkout.show', $hsb_component->id)" />
                        <x-button.delete :item="$hsb_component" />
                    </x-slot:buttons>

                </x-info-panel>
            </x-box>
        </x-page-column>
    </x-container>

@endsection



@section('moar_scripts')
    @can('files', $hsb_component)
        @include ('modals.upload-file', ['item_type' => 'components', 'item_id' => $hsb_component->id])
    @endcan
    @include ('partials.bootstrap-table', ['exportFile' => 'component' . $hsb_component->name . '-export', 'search' => false])
@endsection
