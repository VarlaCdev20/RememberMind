@props(['estado' => 'saludo', 'mensaje' => 'Explora el catálogo y consulta la guía antes de preparar un registro.', 'interactiva' => false])
<aside {{ $attributes->class(['rm-avatar-guia']) }} x-data="rmAvatarGuia(@js($estado), @js($mensaje))"
    @unless($interactiva) x-effect="recibir(@js($estado), @js($mensaje))" @endunless
    @if($interactiva) @rm-guia-contexto.window="if ($el.closest('.rm-guia')?.contains($event.target)) recibir($event.detail.pose, $event.detail.texto)" @endif
    @evaluacion-geriatrica-guardada.window="recibir('exito', 'El servidor confirmó el registro. Puedes consultarlo en el historial.')">
    <div class="rm-avatar-guia__figura" aria-hidden="true" :data-pose="pose">
        @foreach(['saludo', 'explicacion', 'atencion', 'duda', 'exito', 'aviso', 'empatia', 'descanso'] as $pose)
            <img @if($pose === $estado) src="{{ asset('images/remembermind-avatar-'.$pose.'.png') }}" @else :src="pose === '{{ $pose }}' ? @js(asset('images/remembermind-avatar-'.$pose.'.png')) : null" @endif
                alt="" width="480" height="480" decoding="async" x-show="pose === '{{ $pose }}' && !imagenAusente"
                x-transition.opacity @error="imagenAusente = true" @load="imagenAusente = false" class="rm-avatar-guia__imagen" />
        @endforeach
        <i x-show="imagenAusente" x-cloak class="ph-bold ph-book-open rm-avatar-guia__fallback"></i>
        <i class="ph-bold rm-avatar-guia__estado" :class="iconos[pose]"></i>
    </div>
    <div class="rm-avatar-guia__texto"><strong>Guía de RememberMind</strong><p x-text="texto" role="status" aria-live="polite">{{ $mensaje }}</p>
        <button type="button" class="rm-avatar-guia__pausa" @click="pausar()" :aria-pressed="pausada" x-text="pausada ? 'Reactivar ayuda' : 'Pausar ayuda'"></button>
    </div>
</aside>
