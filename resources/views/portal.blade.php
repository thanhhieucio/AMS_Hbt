@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.portal_welcome_title') }}
@parent
@stop

@push('css')
<style>
    .portal-hub {
        max-width: 980px;
        margin: 10px auto 30px;
    }
    .portal-hub-intro {
        text-align: center;
        margin-bottom: 32px;
    }
    .portal-hub-intro h2 {
        font-weight: 600;
        margin-bottom: 8px;
        color: #2c3e50;
    }
    .portal-hub-intro p {
        color: #6c7a89;
        font-size: 15px;
    }
    .portal-hub-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        justify-content: center;
    }
    .portal-card {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 320px;
        padding: 32px 28px;
        border-radius: 16px;
        color: #fff;
        text-decoration: none;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        overflow: hidden;
    }
    .portal-card:hover,
    .portal-card:focus {
        transform: translateY(-6px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.18);
        color: #fff;
        text-decoration: none;
    }
    .portal-card--active {
        background: linear-gradient(135deg, {{ $hsbSettings->header_color ?? '#3c8dbc' }} 0%, #1c5f80 100%);
    }
    .portal-card--soon {
        background: linear-gradient(135deg, #3d9970 0%, #1f6b48 100%);
    }
    .portal-card-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        background: rgba(255, 255, 255, 0.22);
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .portal-card-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.16);
        margin-bottom: 20px;
    }
    .portal-card-icon svg {
        width: 30px;
        height: 30px;
        fill: #fff;
    }
    .portal-card h3 {
        font-size: 19px;
        font-weight: 600;
        margin: 0 0 8px;
        color: #fff;
    }
    .portal-card p {
        font-size: 13.5px;
        opacity: 0.92;
        margin: 0 0 24px;
        line-height: 1.5;
    }
    .portal-card-cta {
        margin-top: auto;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    @media (max-width: 480px) {
        .portal-card {
            width: 100%;
        }
    }
</style>
@endpush

{{-- Page content --}}
@section('content')
<div class="portal-hub">
    <div class="portal-hub-intro">
        <h2>{{ trans('general.portal_welcome_title') }}</h2>
        <p>{{ trans('general.portal_welcome_subtitle') }}</p>
    </div>

    <div class="portal-hub-grid">
        <a href="{{ route('dashboard') }}" class="portal-card portal-card--active">
            <div class="portal-card-icon">
                <x-icon type="assets" />
            </div>
            <h3>{{ $hsbSettings->site_name }}</h3>
            <p>{{ trans('general.portal_asset_management_desc') }}</p>
            <span class="portal-card-cta">
                {{ trans('general.portal_enter_module') }}
                <x-icon type="arrow-circle-right" />
            </span>
        </a>

        <a href="{{ route('dat_rau_huuco') }}" class="portal-card portal-card--soon">
            <span class="portal-card-badge">{{ trans('general.portal_coming_soon') }}</span>
            <div class="portal-card-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 2c-1.1 3.1-3 4.6-3 7.5A3.5 3.5 0 0 0 12.5 13a.5.5 0 0 0 .5-.5V12c-2.5-1-3-3-2-6.2C11.6 4 12.5 3 12 2Z"/>
                    <path d="M12 22c-5 0-8-3.4-8-7.6 0-3.6 2.6-6.2 6.3-6.4a5 5 0 0 1 4.7 2.6c.7 1.3 1 3 1 3s2-1.4 3.5-.2c1.2 1 1.2 3-.5 3.6-1.2.4-2-.2-2-.2s.3 1.7-1 3.1C14.5 21.4 13.2 22 12 22Z"/>
                </svg>
            </div>
            <h3>{{ trans('general.organic_vegetable_order') }}</h3>
            <p>{{ trans('general.portal_organic_vegetable_desc') }}</p>
            <span class="portal-card-cta">
                {{ trans('general.portal_enter_module') }}
                <x-icon type="arrow-circle-right" />
            </span>
        </a>
    </div>
</div>
@stop
