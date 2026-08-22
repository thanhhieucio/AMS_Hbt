@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.organic_vegetable_order') }}
@parent
@stop

@push('css')
<style>
    .coming-soon-box {
        max-width: 640px;
        margin: 40px auto;
        text-align: center;
        padding: 48px 32px;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }
    .coming-soon-box .coming-soon-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 20px;
        border-radius: 18px;
        background: linear-gradient(135deg, #3d9970 0%, #1f6b48 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .coming-soon-box .coming-soon-icon svg {
        width: 38px;
        height: 38px;
        fill: #fff;
    }
    .coming-soon-box h2 {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 12px;
    }
    .coming-soon-box p {
        color: #6c7a89;
        font-size: 14.5px;
        margin-bottom: 24px;
    }
</style>
@endpush

{{-- Page content --}}
@section('content')
<div class="coming-soon-box">
    <div class="coming-soon-icon">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 2c-1.1 3.1-3 4.6-3 7.5A3.5 3.5 0 0 0 12.5 13a.5.5 0 0 0 .5-.5V12c-2.5-1-3-3-2-6.2C11.6 4 12.5 3 12 2Z"/>
            <path d="M12 22c-5 0-8-3.4-8-7.6 0-3.6 2.6-6.2 6.3-6.4a5 5 0 0 1 4.7 2.6c.7 1.3 1 3 1 3s2-1.4 3.5-.2c1.2 1 1.2 3-.5 3.6-1.2.4-2-.2-2-.2s.3 1.7-1 3.1C14.5 21.4 13.2 22 12 22Z"/>
        </svg>
    </div>
    <h2>{{ trans('general.portal_coming_soon_title') }}</h2>
    <p>{{ trans('general.portal_coming_soon_text') }}</p>
    <a href="{{ route('home') }}" class="btn btn-default">
        <x-icon type="angle-left" class="fa-fw" />
        {{ trans('general.portal_back_link') }}
    </a>
</div>
@stop
