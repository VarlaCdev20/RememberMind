@php
    // Pictogramas de orientación, sin criterios diagnósticos ni datos persistidos.
    $zonas = [
        'Región sacra' => [40, 65, 'posterior'], 'Cóccix' => [40, 73, 'posterior'],
        'Glúteo derecho' => [47, 72, 'posterior'], 'Glúteo izquierdo' => [33, 72, 'posterior'],
        'Cadera derecha' => [29, 66, 'frontal'], 'Cadera izquierda' => [51, 66, 'frontal'],
        'Talón derecho' => [48, 106, 'posterior'], 'Talón izquierdo' => [32, 106, 'posterior'],
        'Tobillo derecho' => [32, 101, 'frontal'], 'Tobillo izquierdo' => [48, 101, 'frontal'],
        'Pie derecho' => [32, 109, 'frontal'], 'Pie izquierdo' => [48, 109, 'frontal'],
        'Dedos del pie derecho' => [29, 112, 'frontal'], 'Dedos del pie izquierdo' => [51, 112, 'frontal'],
        'Pierna derecha' => [33, 94, 'frontal'], 'Pierna izquierda' => [47, 94, 'frontal'],
        'Rodilla derecha' => [33, 84, 'frontal'], 'Rodilla izquierda' => [47, 84, 'frontal'],
        'Muslo derecho' => [33, 76, 'frontal'], 'Muslo izquierdo' => [47, 76, 'frontal'],
        'Espalda' => [40, 43, 'posterior'], 'Abdomen' => [40, 56, 'frontal'], 'Tórax' => [40, 39, 'frontal'],
        'Hombro derecho' => [27, 29, 'frontal'], 'Hombro izquierdo' => [53, 29, 'frontal'],
        'Brazo derecho' => [23, 38, 'frontal'], 'Brazo izquierdo' => [57, 38, 'frontal'],
        'Codo derecho' => [20, 47, 'frontal'], 'Codo izquierdo' => [60, 47, 'frontal'],
        'Antebrazo derecho' => [18, 55, 'frontal'], 'Antebrazo izquierdo' => [62, 55, 'frontal'],
        'Mano derecha' => [16, 65, 'frontal'], 'Mano izquierda' => [64, 65, 'frontal'],
        'Región occipital' => [40, 12, 'posterior'], 'Oreja derecha' => [29, 14, 'frontal'],
        'Oreja izquierda' => [51, 14, 'frontal'], 'Cara' => [40, 14, 'frontal'], 'Cuello' => [40, 24, 'frontal'],
    ];
    $zona = $illustrationKind === 'ubicacion' ? ($zonas[$opcion] ?? null) : null;
    $trazo = match ($opcion) {
        'Úlcera por presión' => 'M26 16 V37 M20 31 L26 37 L32 31 M54 16 V37 M48 31 L54 37 L60 31 M15 51 Q40 65 65 51 M22 47 Q40 57 58 47',
        'Herida quirúrgica' => 'M18 40 H62 M25 30 V50 M35 30 V50 M45 30 V50 M55 30 V50',
        'Dehiscencia quirúrgica' => 'M20 28 Q32 40 20 52 M60 28 Q48 40 60 52 M20 28 H28 M52 28 H60 M20 52 H28 M52 52 H60',
        'Desgarro cutáneo' => 'M19 19 H61 V61 H19 Z M22 22 L50 31 L31 50 Z M31 50 L50 31 L51 51',
        'Abrasión' => 'M20 25 L52 19 M22 36 L60 29 M19 48 L56 41 M28 59 L60 53',
        'Laceración' => 'M18 55 L30 35 L38 44 L49 23 L62 30',
        'Herida cortante' => 'M22 58 L58 22 M17 45 L25 51 M55 29 L63 35',
        'Herida punzante' => 'M40 14 V33 M34 27 L40 33 L46 27 M26 49 A14 14 0 1 0 54 49 A14 14 0 1 0 26 49 M36 49 H44',
        'Quemadura' => 'M41 15 Q46 31 57 39 Q67 55 49 64 Q30 70 21 54 Q15 42 28 31 Q27 44 35 42 Q44 34 41 15 Z',
        'Úlcera venosa', 'Úlcera arterial', 'Úlcera de pie diabético' => 'M29 16 H47 L44 52 L59 58 V64 H25 V55 L29 16 M30 48 A7 7 0 1 0 44 48 A7 7 0 1 0 30 48',
        'Lesión por humedad' => 'M28 17 Q12 36 18 44 Q28 56 38 44 Q44 36 28 17 Z M52 31 Q38 48 43 56 Q52 66 61 56 Q66 48 52 31 Z',
        default => 'M20 40 H60 M40 20 V60',
    };
@endphp
@if($zona)
    <span class="rm-wound-option-image rm-wound-option-image--body" aria-hidden="true">
        <svg viewBox="0 0 80 120" focusable="false" fill="none">
            <circle cx="40" cy="13" r="10" fill="var(--rm-surface-soft)" stroke="currentColor" stroke-width="1.5" />
            <path d="M32 24 L28 28 Q23 28 21 36 L13 61 Q11 67 16 69 Q20 70 22 64 L29 44 L28 64 L30 72 L28 105 Q27 110 25 112 H37 L40 81 L43 112 H55 Q53 110 52 105 L50 72 L52 64 L51 44 L58 64 Q60 70 64 69 Q69 67 67 61 L59 36 Q57 28 52 28 L48 24" fill="var(--rm-surface-soft)" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
            <circle cx="{{ $zona[0] }}" cy="{{ $zona[1] }}" r="8" fill="currentColor" opacity=".16" />
            <circle cx="{{ $zona[0] }}" cy="{{ $zona[1] }}" r="4" fill="currentColor" stroke="var(--rm-surface-raised)" stroke-width="1.5" />
        </svg>
        <small>{{ $zona[2] === 'posterior' ? 'Posterior' : 'Frontal' }}</small>
    </span>
@else
    <svg class="rm-wound-option-image" viewBox="0 0 80 80" aria-hidden="true" focusable="false" fill="none">
        <rect x="4" y="4" width="72" height="72" rx="20" fill="currentColor" opacity=".08" />
        <path d="{{ $trazo }}" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
@endif
