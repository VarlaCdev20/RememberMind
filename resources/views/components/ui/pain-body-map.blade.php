@php
    // Atajos de escritura, no catálogo clínico ni valores persistidos separados.
    $common = [
        ['Cabeza', 'M45 8 Q60 0 75 8 L77 28 Q76 43 60 46 Q44 43 43 28 Z', 60, 25],
        ['Cuello', 'M51 45 H69 V58 H51 Z', 60, 51],
        ['Hombro derecho', 'M37 56 L51 54 V72 H31 Z', 39, 64],
        ['Hombro izquierdo', 'M69 54 L83 56 L89 72 H69 Z', 81, 64],
        ['Brazo derecho', 'M30 72 H42 L36 110 L22 108 Z', 31, 90],
        ['Brazo izquierdo', 'M78 72 H90 L98 108 L84 110 Z', 89, 90],
        ['Antebrazo y mano derechos', 'M22 108 L36 110 L28 151 L14 155 Z', 25, 129],
        ['Antebrazo y mano izquierdos', 'M84 110 L98 108 L106 155 L92 151 Z', 95, 129],
        ['Cadera derecha', 'M42 126 H60 V150 H38 Z', 48, 137],
        ['Cadera izquierda', 'M60 126 H78 L82 150 H60 Z', 72, 137],
        ['Muslo derecho', 'M38 150 H58 L55 186 H36 Z', 46, 168],
        ['Muslo izquierdo', 'M62 150 H82 L84 186 H65 Z', 74, 168],
        ['Rodilla derecha', 'M36 186 H55 V203 H36 Z', 46, 194],
        ['Rodilla izquierda', 'M65 186 H84 V203 H65 Z', 74, 194],
        ['Pierna derecha', 'M36 203 H55 L52 236 H39 Z', 46, 219],
        ['Pierna izquierda', 'M65 203 H84 L81 236 H68 Z', 74, 219],
        ['Pie derecho', 'M39 236 H52 V247 H32 L33 241 Z', 43, 242],
        ['Pie izquierdo', 'M68 236 H81 L87 241 L88 247 H68 Z', 77, 242],
    ];
    // En vista posterior los lados anatómicos se invierten en la imagen.
    $views = [
        'front' => ['label' => 'Frontal', 'zones' => array_merge($common, [
            ['Pecho', 'M43 58 H77 V94 H43 Z', 60, 77],
            ['Abdomen', 'M43 94 H77 V125 H43 Z', 60, 110],
        ])],
        'back' => ['label' => 'Posterior', 'zones' => array_merge($common, [
            ['Espalda', 'M43 58 H77 V100 H43 Z', 60, 78],
            ['Zona lumbar', 'M43 100 H77 V126 H43 Z', 60, 113],
        ])],
    ];
@endphp
<div class="rm-dolor__map-tabs" role="group" aria-label="Vista corporal">
    @foreach($views as $view => $definition)
        <button type="button" :aria-pressed="bodyView === '{{ $view }}'" @click="bodyView = '{{ $view }}'">{{ $definition['label'] }}</button>
    @endforeach
</div>
<div class="rm-dolor__body-map">
    @foreach($views as $view => $definition)
        <figure :data-current="bodyView === '{{ $view }}' ? 'true' : 'false'">
            <svg viewBox="0 0 120 260" role="group" aria-label="Mapa corporal {{ mb_strtolower($definition['label']) }}">
                <path class="rm-dolor__silhouette" d="M60 4 C39 4 39 31 48 42 L51 53 L35 57 Q27 60 25 79 L14 148 Q12 161 23 158 L37 111 L40 123 L35 154 L36 191 L39 236 L32 241 L32 249 H53 L56 198 L60 158 L64 198 L67 249 H88 L88 241 L81 236 L84 191 L85 154 L80 123 L83 111 L97 158 Q108 161 106 148 L95 79 Q93 60 85 57 L69 53 L72 42 C81 31 81 4 60 4 Z" />
                @foreach($definition['zones'] as [$label, $path, $x, $y])
                    @php($zone = $view === 'back' ? strtr($label, ['derechos' => 'izquierdos', 'izquierdos' => 'derechos', 'derecha' => 'izquierda', 'izquierda' => 'derecha', 'derecho' => 'izquierdo', 'izquierdo' => 'derecho']) : $label)
                    <g class="rm-dolor__zone" role="button" tabindex="0" aria-label="Seleccionar {{ mb_strtolower($zone) }} · {{ mb_strtolower($definition['label']) }}" :aria-pressed="zones.includes(@js($zone))"
                       @click="toggleZone(@js($zone))" @keydown.enter.prevent="toggleZone(@js($zone))" @keydown.space.prevent="toggleZone(@js($zone))">
                        <title>{{ $zone }}</title>
                        <path d="{{ $path }}" />
                        <circle class="rm-dolor__hotspot" cx="{{ $x }}" cy="{{ $y }}" r="6" />
                    </g>
                @endforeach
            </svg>
            <figcaption>{{ $definition['label'] }}</figcaption>
        </figure>
    @endforeach
</div>
<details class="rm-dolor__zone-list">
    <summary>Seleccionar una zona por nombre</summary>
    <div>
        @foreach(array_unique(array_column(array_merge($common, $views['front']['zones'], $views['back']['zones']), 0)) as $zone)
            <button type="button" :aria-pressed="zones.includes(@js($zone))" @click="toggleZone(@js($zone))">{{ $zone }}</button>
        @endforeach
    </div>
</details>
