@props([
    'name',
    'label' => null,
    'id' => null,
    'value' => null,
    'placeholder' => 'Write something...',
    'help' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $id ??= str_replace(['[]', '[', ']', '.'], ['', '-', '', '-'], $name);
    $currentValue = old($name, $value);
@endphp

<x-form.field :name="$name" :label="$label" :id="$id" :required="$required" :error="$error" :help="$help">
    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        class="js-rich-text-editor"
        data-placeholder="{{ $placeholder }}"
        @required($required)
        @disabled($disabled)
    >{{ $currentValue }}</textarea>
</x-form.field>
