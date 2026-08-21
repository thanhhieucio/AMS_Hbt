@props(['name', 'bag' => 'default'])
@error($name, $bag)
    <span class="alert-msg" role="alert" aria-live="assertive">{{ $message }}</span>
@enderror
