<a href="{{ $href }}" {{ $attributes->merge(['class' => 'list-page__detail-link']) }}>
    {{ $slot->isEmpty() ? '詳細' : $slot }}
</a>