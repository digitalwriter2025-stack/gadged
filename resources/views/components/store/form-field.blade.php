@props(['name', 'label', 'type' => 'text', 'value' => null, 'bag' => 'default', 'required' => false, 'hint' => null])

@php
    $error = $errors->getBag($bag)->first($name);
    $id = $attributes->get('id', $bag === 'default' ? $name : "{$bag}-{$name}");
    $describedBy = $error ? "{$id}-error" : ($hint ? "{$id}-hint" : null);
@endphp

<div>
    <label for="{{ $id }}" class="block text-xs font-medium text-gray-700">
        {{ $label }}
        @if ($required)
            <span class="text-price">*</span>
        @endif
    </label>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ $value }}" @endif
        @required($required)
        @if ($error) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('id')->class('mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition focus:border-builder focus:ring-2 focus:ring-builder/20 focus:outline-none aria-invalid:border-price') }}
    >
    @if ($error)
        <p id="{{ $id }}-error" class="mt-1 text-[11px] text-price" role="alert">{{ $error }}</p>
    @elseif ($hint)
        <p id="{{ $id }}-hint" class="mt-1 text-[11px] text-gray-500">{{ $hint }}</p>
    @endif
</div>
