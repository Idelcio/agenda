@props([
    'size' => 50, // tamanho padrão em pixels
])

@php
    // Garante que o tamanho é numérico e converte pra inteiro
    $pixelSize = is_numeric($size) ? (int) $size : 48;
@endphp

<img src="{{ asset('logo2.png') }}" alt="{{ config('app.name', 'Agendoo') }} logo" width="{{ $pixelSize }}"
    height="{{ $pixelSize }}" style="width: {{ $pixelSize }}px; height: auto;"
    {{ $attributes->merge(['class' => 'block object-contain']) }}>
