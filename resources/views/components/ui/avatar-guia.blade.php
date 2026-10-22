@props(['estado' => 'saludo', 'mensaje' => 'Explora el catálogo y consulta la guía antes de preparar un registro.'])
<aside {{ $attributes->class(['rm-avatar-guia']) }} x-data="rmAvatarGuia(@js($estado), @js($mensaje))"
    x-effect="recibir(@js($estado), @js($mensaje))"
    @evaluacion-geriatrica-guardada.window="recibir('exito', 'El servidor confirmó el registro. Puedes consultarlo en el historial.')">
    <div class="rm-avatar-guia__figura" aria-hidden="true" :data-pose="pose">
        {{-- Personaje vectorial original: brote lector. No reutiliza la mascota del prototipo. --}}
        <svg viewBox="0 0 100 112" width="76" height="86" focusable="false">
            <ellipse cx="50" cy="104" rx="29" ry="4" class="rm-avatar-guia__sombra"/>
            <path d="M31 95 Q25 57 50 56 Q75 57 69 95Z" class="rm-avatar-guia__cuerpo"/>
            <path d="M50 57 C16 56 16 23 20 14 C44 11 56 29 50 57Z" class="rm-avatar-guia__hoja"/>
            <path d="M50 57 C76 54 84 35 79 18 C56 15 45 34 50 57Z" class="rm-avatar-guia__hoja rm-avatar-guia__hoja--segunda"/>
            <path d="M25 23 Q42 34 50 57 M73 26 Q56 41 50 57" fill="none" class="rm-avatar-guia__nervio" stroke-width="2"/>
            <circle cx="39" cy="68" r="3" class="rm-avatar-guia__tinta"/><circle cx="61" cy="68" r="3" class="rm-avatar-guia__tinta"/>
            <path :d="pose === 'duda' || pose === 'aviso' ? 'M44 80 Q50 75 56 80' : pose === 'descanso' ? 'M44 77 L56 77' : 'M43 77 Q50 85 57 77'" fill="none" class="rm-avatar-guia__nervio" stroke-width="2.5" stroke-linecap="round"/>
            <path d="M23 85 Q35 80 50 87 Q65 80 77 85 L74 101 Q60 96 50 103 Q36 96 26 101Z" class="rm-avatar-guia__libro" stroke-width="2"/>
            <path d="M50 87 V103 M31 89 L43 91 M58 91 L69 89" fill="none" class="rm-avatar-guia__nervio" stroke-width="1.5"/>
            <path d="M28 73 Q12 70 17 54" fill="none" class="rm-avatar-guia__brazo" stroke-width="5" stroke-linecap="round"/>
        </svg>
        <i class="ph-bold rm-avatar-guia__estado" :class="iconos[pose]"></i>
    </div>
    <div class="rm-avatar-guia__texto"><strong>Guía de RememberMind</strong><p x-text="texto" role="status" aria-live="polite">{{ $mensaje }}</p>
        <button type="button" class="rm-avatar-guia__pausa" @click="pausar()" x-text="pausada ? 'Reactivar ayuda' : 'Pausar ayuda'"></button>
    </div>
</aside>
