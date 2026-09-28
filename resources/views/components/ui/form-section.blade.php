@props(['title', 'description' => null, 'columns' => 2])
<fieldset class="rm-form-section border border-[var(--rm-border-soft)] rounded-[var(--rm-radius-card,16px)] bg-[var(--rm-surface)] p-5 mb-5">
    <legend class="px-2 text-sm font-extrabold text-[var(--rm-text-primary)] uppercase tracking-wide">
        {{ $title }}
    </legend>
    @if($description)
        <p class="mb-4 text-xs font-medium text-[var(--rm-text-secondary)]">
            {{ $description }}
        </p>
    @endif
    <div @class(['grid gap-4', 'md:grid-cols-2' => $columns === 2, 'md:grid-cols-3' => $columns === 3])>
        {{ $slot }}
    </div>
</fieldset>
