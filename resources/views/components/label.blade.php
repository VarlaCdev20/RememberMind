@props(['value'])

<label {{ $attributes->merge(['class' => 'rm-label font-bold text-sm text-[var(--rm-text-primary)] mb-1 block']) }}>
    {{ $value ?? $slot }}
</label>
