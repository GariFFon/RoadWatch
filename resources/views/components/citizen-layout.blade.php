@props(['title' => null])

<x-layouts.citizen :title="$title ?? null">
    {{ $slot }}
</x-layouts.citizen>
