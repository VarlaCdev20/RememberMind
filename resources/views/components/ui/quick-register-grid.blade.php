@props(['sections' => [], 'onSelect'])
<div class="rm-quick-register">
    @foreach($sections as $section)
        <section aria-labelledby="quick-register-section-{{ $loop->index }}">
            <h4 id="quick-register-section-{{ $loop->index }}">{{ $section['title'] }}</h4>
            <div class="rm-quick-register__grid {{ ($section['columns'] ?? 3) === 2 ? 'rm-quick-register__grid--care' : '' }}">
                @foreach($section['actions'] as $action)
                    @can($action['permiso'])
                        @php($available = (bool) ($action['disponible'] ?? true))
                        <x-ui.register-action-card :icon="$action['icon']" :label="$action['label']" :tone="$action['tone'] ?? 'clinical'" :disabled="!$available" :hint="$available ? null : ($action['hint'] ?? 'Sin formulario directo aquí')" wire:click="{{ $onSelect }}('{{ $action['tipo'] }}')" />
                    @endcan
                @endforeach
            </div>
        </section>
    @endforeach
</div>
