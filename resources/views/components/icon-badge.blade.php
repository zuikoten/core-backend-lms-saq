@props(['icon', 'color' => 'blue'])

@php
$colors = [
    'blue' => 'bg-blue-50 text-blue-600',
    'green' => 'bg-green-50 text-green-600',
    'amber' => 'bg-amber-50 text-amber-600',
    'red' => 'bg-red-50 text-red-600',
    'indigo' => 'bg-indigo-50 text-indigo-600',
];
@endphp

<div class="w-9 h-9 rounded-[10px] {{ $colors[$color] ?? $colors['blue'] }} flex items-center justify-center">
    <i class="ti ti-{{ $icon }} text-[17px]"></i>
</div>
