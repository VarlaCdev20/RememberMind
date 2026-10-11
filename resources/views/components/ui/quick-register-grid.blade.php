@props(['sections' => [], 'onSelect'])
<div class="rm-quick-register">
    @foreach($sections as $section)
        <section aria-labelledby="quick-register-section-{{ $loop->index }}">
            <h4 id="quick-register-section-{{ $loop->index }}">{{ $section['title'] }}</h4>
            <div class="rm-quick-register__grid {{ ($section['columns'] ?? 3) === 2 ? 'rm-quick-register__grid--care' : '' }}">
                @foreach($section['actions'] as $action)
                    @can($action['permiso'])
                        @if(auth()->user()->can($action['permisos_adicionales'] ?? []))
                            @php($available = (bool) ($action['disponible'] ?? true))
                            @if($available && !empty($action['href']))
                                <x-ui.register-action-card :icon="$action['icon']" :label="$action['label']" :tone="$action['tone'] ?? 'clinical'" :href="$action['href']" :hint="$action['hint'] ?? null" />
                            @else
                                <x-ui.register-action-card :icon="$action['icon']" :label="$action['label']" :tone="$action['tone'] ?? 'clinical'" :disabled="!$available" :hint="$action['hint'] ?? (!$available ? 'No disponible en este momento' : null)" wire:click="{{ $onSelect }}('{{ $action['tipo'] }}')" />
                            @endif
                        @endif
                    @endcan
                @endforeach
            </div>
        </section>
    @endforeach
</div>
