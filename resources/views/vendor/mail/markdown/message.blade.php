@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
@if (($hsbSettings->show_images_in_email=='1' ) && ($hsbSettings::setupCompleted()))

@if ($hsbSettings->brand == '3')
@if ($hsbSettings->logo!='')
<img class="navbar-brand-img logo" src="{{ Storage::disk('public')->url($hsbSettings->logo) }}" alt="">
@endif
{{ $hsbSettings->site_name }}

@elseif ($hsbSettings->brand == '2')
@if ($hsbSettings->logo!='')
<img class="navbar-brand-img logo" src="{{ Storage::disk('public')->url($hsbSettings->logo) }}" alt="">
@endif
@else
{{ $hsbSettings->site_name }}
@endif
@else
HSB-IT
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
