@php
    // Previous URL from browser / Referer header
    $prev = url()->previous();
    $current = url()->current();

    // Default: use previous URL
    $href = $prev;

    // If previous == current (e.g. opened directly, refresh, or missing Referer)
    // and you passed a default route → use that as a safe fallback.
    if ($defaultRoute && $prev === $current) {
        $href = route($defaultRoute);
    }
@endphp

<a href="{{ $href }}" class="btn btn-primary {{ $class }}">
    {{ $slot ?: 'Back' }}
</a>
