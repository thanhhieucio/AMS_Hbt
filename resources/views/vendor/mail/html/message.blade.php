@component('mail::layout')
{{-- Header --}}
@slot('header')

{{-- Check that the $hsbSettings variable is set, images are set to be shown, and setup is complete --}}
<style>

    th, td {
        vertical-align: top;
    }
    hr {
        display: block;
        height: 1px;
        border: 0;
        border-top: 1px solid #edeff2;
        margin: 1em 0;
        padding: 0;
    }
</style>

@if (isset($hsbSettings) && ($hsbSettings::setupCompleted()))

    @if ($hsbSettings->show_url_in_emails=='1' )
        @component('mail::header', ['url' => config('app.url')])
    @else
        @component('mail::header', ['url' => ''])
    @endif

    {{-- Show images in email!  --}}
    @if (($hsbSettings->show_images_in_email=='1') && ($hsbSettings->email_logo!='') && ($hsbSettings->brand != '1'))

        {{-- $hsbSettings->brand = 1 = Text  --}}
        {{-- $hsbSettings->brand = 2 = Logo  --}}
        {{-- $hsbSettings->brand = 3 = Logo + Text  --}}
        @if ($hsbSettings->brand == '3')

            <img style="max-height: 100px; vertical-align:middle;" src="{{ \Storage::disk('public')->url(e($hsbSettings->email_logo)) }}" alt="">
            <br><br>
            {{ $hsbSettings->site_name }}
            <br><br>

        {{-- else if branding type is just logo --}}
        @elseif ($hsbSettings->brand == '2')
           <img style="max-height: 100px; vertical-align:middle;" src="{{ \Storage::disk('public')->url(e($hsbSettings->email_logo)) }}" alt="">
        @endif

    @else
        {{ $hsbSettings->site_name ?? config('app.name') }}
    @endif

@endif
@endcomponent
@endslot

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endisset

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
@if($hsbSettings::setupCompleted())
© {{ date('Y') }} {{ $hsbSettings->site_name }}. {{ trans('mail.rights_reserved') }}
@else
© {{ date('Y') }} HSB-IT. {{ trans('mail.rights_reserved') }}
@endif

@if ($hsbSettings->privacy_policy_link!='')
<a href="{{ $hsbSettings->privacy_policy_link }}">{{ trans('admin/settings/general.privacy_policy') }}</a>
@endif

@endcomponent
@endslot
@endcomponent
