{{-- Patrón Canónico de Tarjeta de Entidad (Agnóstica de Dominio) --}}
@props([
    'title' => null,
    'subtitle' => null,
    'status' => null,
    'statusVariant' => 'neutral',
    'interactive' => false,
    'active' => false,
    'accent' => null,
])

@php
    $accentBorder = match($accent) {
        'terracota', 'primary' => 'border-l-4 border-l-[var(--rm-role-primary)]',
        'danger' => 'border-l-4 border-l-[var(--rm-danger)]',
        'warning' => 'border-l-4 border-l-[var(--rm-warning)]',
        'success' => 'border-l-4 border-l-[var(--rm-success)]',
        'info' => 'border-l-4 border-l-[var(--rm-info)]',
        default => '',
    };

    $activeClasses = $active
        ? 'ring-2 ring-[var(--rm-role-primary)] border-[var(--rm-role-primary)] bg-[var(--rm-surface-soft)]'
        : 'border-[var(--rm-border)] bg-[var(--rm-surface)]';

    $interactiveClasses = $interactive
        ? 'cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-[var(--rm-role-primary)]'
        : 'shadow-2xs';
@endphp

<article {{ $attributes->merge(['class' => "rm-person-entity-card rm-entity-card border p-4 sm:p-5 flex flex-col justify-between gap-3 relative font-sans {$accentBorder} {$activeClasses} {$interactiveClasses}"]) }} data-entity="user">
    {{-- Indicador lateral activo si corresponde --}}
    @if($active)
        <span class="absolute left-0 top-4 bottom-4 w-1.5 rounded-r-full bg-[var(--rm-role-primary)]"></span>
    @endif

    {{-- Cabecera: Avatar/Icono + Títulos + Estado --}}
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            @if(isset($avatar))
                <div class="shrink-0">
                    {{ $avatar }}
                </div>
            @endif

            <div class="min-w-0 flex-1">
                @if(isset($title))
                    {{ $title }}
                @elseif($title)
                    <h4 class="font-bold text-sm sm:text-base text-[var(--rm-text-primary)] truncate leading-tight">
                        {{ $title }}
                    </h4>
                @endif

                @if(isset($subtitle))
                    {{ $subtitle }}
                @elseif($subtitle)
                    <p class="text-xs text-[var(--rm-text-secondary)] truncate mt-0.5">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Estado / Badge --}}
        @if(isset($status))
            <div class="shrink-0">
                {{ $status }}
            </div>
        @elseif($status)
            <div class="shrink-0">
                <span class="rm-badge text-[11px] font-extrabold px-2.5 py-0.5 rounded-full {{ match($statusVariant) {
                    'primary', 'terracota' => 'bg-[var(--rm-primary)] text-white',
                    'success' => 'bg-[var(--rm-success-soft)] text-[var(--rm-success)] border border-[var(--rm-success)]',
                    'warning' => 'bg-[var(--rm-warning-soft)] text-[var(--rm-warning)] border border-[var(--rm-warning)]',
                    'danger' => 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]',
                    'info' => 'bg-[var(--rm-info-soft)] text-[var(--rm-info)] border border-[var(--rm-info)]',
                    default => 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-secondary)] border border-[var(--rm-border)]'
                } }}">
                    {{ $status }}
                </span>
            </div>
        @endif
    </div>

    {{-- Metadatos --}}
    @if(isset($metadata))
        <div class="rm-entity-card-meta py-1 border-t border-[var(--rm-border-soft)] text-xs text-[var(--rm-text-secondary)]">
            {{ $metadata }}
        </div>
    @endif

    {{-- Contenido Principal --}}
    @if(trim($slot) !== '')
        <div class="rm-entity-card-body text-xs text-[var(--rm-text-primary)] leading-relaxed">
            {{ $slot }}
        </div>
    @endif

    {{-- Acciones / Footer --}}
    @if(isset($actions))
        <div class="rm-entity-card-actions pt-2 border-t border-[var(--rm-border-soft)] flex items-center justify-end gap-2 flex-wrap">
            {{ $actions }}
        </div>
    @endif
</article>
