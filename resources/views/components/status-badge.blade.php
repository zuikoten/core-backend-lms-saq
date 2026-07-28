@props(['status' => 'success'])

@php
$map = [
    'success' => ['bg-green-50 text-green-600', 'Lunas'],
    'warning' => ['bg-amber-50 text-amber-600', 'Sebagian'],
    'danger' => ['bg-red-50 text-red-600', 'Belum Bayar'],
];
[$classes, $defaultLabel] = $map[$status] ?? $map['success'];
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $classes }}">
    {{ $slot->isEmpty() ? $defaultLabel : $slot }}
</span>
