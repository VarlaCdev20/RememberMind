@props([
    'usuario' => null,
    'image' => 'images/FOTOS CENTRO DE ADULTOS MAYORES/621786801_1404497435021508_7880315777607437580_n.jpg',
    'secondaryImage' => 'images/FOTOS CENTRO DE ADULTOS MAYORES/558487013_1337134818424437_2282337776297854403_n.jpg',
    'estado' => null,
    'modo' => null,
])

@php
    $usuario = $usuario ?? auth()->user();
    $persona = $usuario?->personal ?: $usuario?->contactos()->first();
    $nombreCompleto = trim(implode(' ', array_filter([
        $persona?->nombres,
        $persona?->apellido_paterno,
        $persona?->apellido_materno,
    ])));
    if ($nombreCompleto !== '') {
        $nombreCompleto = mb_convert_case(mb_strtolower($nombreCompleto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $nombreCompleto = preg_replace_callback(
            '/\b(?:De|Del|La|Las|Los|Y)\b/u',
            static fn ($coincidencia) => mb_strtolower($coincidencia[0], 'UTF-8'),
            $nombreCompleto
        );
    } else {
        $nombreCompleto = 'equipo de Enfermería';
    }
    $nombreLargo = mb_strlen($nombreCompleto, 'UTF-8') > 32;
    $rol = $usuario?->getRoleNames()->first();
    $rolVisible = $rol ? mb_convert_case(str_replace('_', ' ', $rol), MB_CASE_TITLE, 'UTF-8') : 'Personal';
    $zonaHoraria = config('app.timezone');
    $contextoTurno = $estado === 'SIN_JORNADA_ACTIVA'
        ? 'No tienes un turno activo en este momento. Revisa los indicadores del centro.'
        : ($modo === 'FUERA_DE_TURNO' ? 'Turno del equipo disponible en modo consulta. Revisa los indicadores del centro.' : 'Cuidados y seguimiento de tu turno. Revisa pendientes y alertas.');
@endphp

<x-ui.card {{ $attributes->class(['rm-nursing-dashboard__welcome rm-nursing-welcome', 'rm-nursing-welcome--long-name' => $nombreLargo]) }} aria-labelledby="nursing-welcome-title" data-time-zone="{{ $zonaHoraria }}">
    <div class="rm-nursing-welcome__intro">
        <div class="rm-nursing-welcome__copy">
            <span class="rm-nursing-welcome__role"><i class="ph-bold ph-first-aid-kit" aria-hidden="true"></i>{{ $rolVisible }}</span>
            <h1 id="nursing-welcome-title" aria-label="Bienvenido, {{ $nombreCompleto }}">
                <span class="rm-nursing-welcome__greeting">Bienvenido,</span>
                <span class="rm-nursing-welcome__name">{{ $nombreCompleto }}</span>
            </h1>
            <p class="rm-nursing-welcome__context"><i class="ph-bold ph-heartbeat" aria-hidden="true"></i>{{ $contextoTurno }}</p>
        </div>
    </div>

    <div class="rm-nursing-welcome__visual">
        <div class="rm-nursing-welcome__image">
            <img src="{{ asset($image) }}" alt="Actividad y acompañamiento en Los Almendros" loading="eager" decoding="async" fetchpriority="high">
        </div>
        <div class="rm-nursing-welcome__image-detail">
            <img src="{{ asset($secondaryImage) }}" alt="Momentos de cuidado y convivencia en Los Almendros" loading="lazy" decoding="async">
        </div>
        <span class="rm-nursing-welcome__image-icon" aria-hidden="true"><i class="ph-bold ph-stethoscope"></i></span>
    </div>

    <div class="rm-nursing-welcome__tools"
         x-data="{
            hora: '', fecha: '', temporizador: null, esperaMinuto: null, zona: '{{ $zonaHoraria }}',
            actualizar() {
                const ahora = new Date();
                this.hora = new Intl.DateTimeFormat('es-BO', { timeZone: this.zona, hour: '2-digit', minute: '2-digit', hour12: false }).format(ahora);
                this.fecha = new Intl.DateTimeFormat('es-BO', { timeZone: this.zona, weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }).format(ahora);
            },
            init() {
                this.actualizar();
                this.esperaMinuto = setTimeout(() => {
                    this.actualizar();
                    this.temporizador = setInterval(() => this.actualizar(), 60000);
                }, 60000 - Date.now() % 60000);
            },
            destroy() { clearTimeout(this.esperaMinuto); clearInterval(this.temporizador); }
         }">
        <div class="rm-nursing-welcome__tools-row">
            <div class="rm-nursing-welcome__clock" aria-label="Hora y fecha actuales">
                <span>Hora actual</span>
                <strong x-text="hora">{{ now()->timezone($zonaHoraria)->format('H:i') }}</strong>
                <small x-text="fecha">{{ now()->timezone($zonaHoraria)->locale('es')->translatedFormat('D d M Y') }}</small>
            </div>
        </div>
    </div>
</x-ui.card>
