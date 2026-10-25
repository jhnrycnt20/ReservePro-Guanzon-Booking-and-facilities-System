@props(['field', 'show' => true])

@php
    $message = $show ? ($errors->first($field) ?: $errors->first($field.'.*')) : null;
@endphp

@if($message)
    <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
@endif
