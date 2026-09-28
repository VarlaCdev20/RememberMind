@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'rm-error text-xs font-semibold text-[var(--rm-danger)] mt-1.5 flex items-center gap-1']) }}>
        <i class="ph-bold ph-warning-circle text-sm shrink-0"></i>
        <span>{{ $message }}</span>
    </p>
@enderror
