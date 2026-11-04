@props(['atencion', 'puedeVerInformante' => false])
<dl {{ $attributes->class(['rm-captura__guia-grid']) }}>
    <div><dt>Escolaridad informada</dt><dd>{{ $atencion->escolaridad_anios === null ? 'No registrada' : $atencion->escolaridad_anios.' años' }}</dd></div>
    <div><dt>Lectura referida</dt><dd>{{ $atencion->puede_leer === null ? 'No se conoce' : ($atencion->puede_leer ? 'Sí' : 'No') }}</dd></div>
    <div><dt>Escritura referida</dt><dd>{{ $atencion->puede_escribir === null ? 'No se conoce' : ($atencion->puede_escribir ? 'Sí' : 'No') }}</dd></div>
    <div><dt>Idioma principal de esa atención</dt><dd class="break-words">{{ $atencion->idioma_principal_evaluacion ?? 'No registrado' }}</dd></div>
    @if($puedeVerInformante)
        <div><dt>Informante principal de esa atención</dt><dd class="break-words">{{ $atencion->informantePrincipal?->contacto?->nombre_completo ?? ($atencion->cod_residente_contacto_informante ? 'Contacto vinculado no disponible' : 'No registrado') }}</dd></div>
        <div><dt>¿Refiere conocer el nivel previo?</dt><dd>{{ $atencion->informante_conoce_nivel_previo === null ? 'No se conoce' : ($atencion->informante_conoce_nivel_previo ? 'Sí' : 'No') }}</dd></div>
    @else
        <div><dt>Informante</dt><dd>Consulta restringida por permisos de contactos.</dd></div>
    @endif
</dl>
