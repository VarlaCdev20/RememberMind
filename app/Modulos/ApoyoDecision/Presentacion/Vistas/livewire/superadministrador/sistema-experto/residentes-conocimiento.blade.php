<div class="rm-expert-admin-residents" x-data="{ pestana: 'fuentes' }">
    <div class="rm-expert-admin-residents__directory rm-card">
        <header><p class="rm-expert-eyebrow">Expedientes admitidos</p><h2>Residentes</h2><p>Seleccione una persona para consultar sus propias fuentes.</p></header>
        <label for="expert-admin-resident-search">Buscar nombre o código</label>
        <input id="expert-admin-resident-search" class="rm-input w-full" type="search" maxlength="100" wire:model.live.debounce.300ms="buscar">
        <p class="rm-caption" role="status">{{ $residentes->total() }} residentes encontrados</p>
        <div class="rm-expert-admin-residents__list" aria-label="Seleccionar residente">
            @forelse($residentes as $persona)
                <button type="button" wire:key="expert-admin-resident-{{ $persona->getKey() }}" wire:click="seleccionar(@js($persona->getKey()))" aria-pressed="{{ $seleccionado === $persona->getKey() ? 'true' : 'false' }}"><span class="rm-expert-admin-residents__avatar" aria-hidden="true"><i class="ph ph-user"></i></span><span><strong>{{ $persona->nombre_completo }}</strong><small>{{ $persona->edad !== null ? $persona->edad.' años' : 'Edad no disponible' }} · {{ $persona->ubicacion_formateada }}</small><small>{{ $persona->getKey() }}</small></span><i class="ph ph-caret-right" aria-hidden="true"></i></button>
            @empty
                <x-ui.empty-state compact title="Sin residentes que coincidan" description="Cambie el nombre o código de la búsqueda." />
            @endforelse
        </div>
        {{ $residentes->links() }}
    </div>
    <div class="rm-expert-admin-residents__detail" wire:loading.class="opacity-60" wire:target="seleccionar">
        @if(!$residente)
            <div class="rm-card rm-expert-admin-residents__welcome"><i class="ph ph-user-focus" aria-hidden="true"></i><h2>Conozca el recorrido de cada residente</h2><p>Elija una persona del listado. Podrá consultar sus aplicaciones originales, observaciones y resultados conservados, con su fecha y procedencia.</p><ol><li>Seleccione un residente</li><li>Abra una fuente y sus conexiones</li><li>Compruebe los datos originales</li></ol></div>
        @else
            <header class="rm-card rm-expert-admin-residents__identity"><span class="rm-expert-admin-residents__avatar" aria-hidden="true"><i class="ph ph-user"></i></span><div><p class="rm-expert-eyebrow">Consulta de superadministración</p><h2>{{ $residente->nombre_completo }}</h2><p>{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no disponible' }} · {{ $residente->ubicacion_formateada }}</p><small>{{ $residente->getKey() }}</small></div><span class="rm-expert-origin"><i class="ph ph-eye" aria-hidden="true"></i>Solo lectura</span></header>
            <div class="rm-expert-local-tabs" role="group" aria-label="Datos del residente">
                <button type="button" @click="pestana = 'fuentes'" :aria-pressed="pestana === 'fuentes'">Fuentes y conexiones</button>
                <button type="button" @click="pestana = 'resultados'" :aria-pressed="pestana === 'resultados'">Resultados conservados</button>
                <button type="button" @click="pestana = 'observaciones'" :aria-pressed="pestana === 'observaciones'">Observaciones</button>
            </div>
            <section x-show="pestana === 'fuentes'" class="rm-card rm-expert-admin-residents__panel" aria-label="Fuentes del expediente">
                <h3>De dónde procede su información</h3><p>Registros originales de este expediente. Su existencia no acredita un resultado cognitivo ni una evaluación profesional.</p>
                @if(!$puedeFuentes)
                    <div class="rm-alert rm-alert-warning" role="status">Su cuenta no tiene todas las lecturas requeridas de instrumentos. Los resultados autorizados permanecen en su pestaña.</div>
                @elseif($errorFuentes)
                    <div class="rm-alert rm-alert-danger" role="alert">No se pudieron consultar las fuentes. <button type="button" class="rm-btn rm-btn-secondary" wire:click="reintentar">Reintentar consulta</button></div>
                @else
                    <div class="rm-expert-source-grid">
                        @forelse($capturas['capturas'] ?? [] as $captura)
                            <button type="button" data-expert-resident-source wire:click="abrirFuente(@js($captura['cod_aplicacion']))" aria-pressed="{{ $fuente === $captura['cod_aplicacion'] ? 'true' : 'false' }}"><i class="ph ph-file-text" aria-hidden="true"></i><span><strong>{{ $captura['nombre_instrumento'] ?? 'Instrumento sin nombre' }}</strong><small>{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($captura['fecha_hora']) }}</small><small>{{ $captura['autor'] ?: 'Autoría no disponible' }}</small><small>{{ $captura['estado_original'] ?? 'Estado no disponible' }} · {{ $captura['cod_aplicacion'] }}</small></span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></button>
                        @empty
                            <x-ui.empty-state compact title="Sin aplicaciones registradas" description="No hay aplicaciones originales disponibles para este residente. La ausencia no equivale a un resultado normal." />
                        @endforelse
                    </div>
                    @if($detalleFuente)
                        <div class="rm-expert-source-detail" role="region" aria-label="Detalle de la fuente seleccionada"><h4>{{ $detalleFuente['nombre_instrumento'] ?? 'Fuente seleccionada' }}</h4><p>Aplicación {{ $detalleFuente['cod_aplicacion'] }} · {{ $residente->nombre_completo }}</p><details open><summary>Procedencia original</summary>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $detalleFuente['procedencia'] ?? []])</details><details><summary>Respuestas originales autorizadas</summary>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $detalleFuente['respuestas_originales'] ?? []])</details><details><summary>Identidad y versión del instrumento</summary><dl class="rm-lab-data"><div><dt>Instrumento</dt><dd>{{ $detalleFuente['cod_instrumento'] }}</dd></div><div><dt>Versión</dt><dd>{{ $detalleFuente['version_instrumento'] ?: 'No documentada' }}</dd></div><div><dt>Profesional</dt><dd>{{ $detalleFuente['autor'] ?: 'No disponible' }}</dd></div></dl></details></div>
                    @endif
                    @foreach($conexionesResidente['elementos'] ?? [] as $elemento)
                        <details class="rm-expert-source-detail"><summary>{{ $elemento['nombre'] }} · Ver mapa de procedencia</summary>
                            @include('apoyodecision::livewire.superadministrador.sistema-experto.mapa-lectura', ['grafoLectura' => $elemento['grafo']])
                        </details>
                    @endforeach
                @endif
            </section>
            <section x-show="pestana === 'resultados'" x-cloak class="rm-card rm-expert-admin-residents__panel" aria-label="Evaluaciones conservadas">
                <h3>Evaluaciones conservadas</h3><p>La historia conserva su fecha y versión. Las fuentes actuales no completan retroactivamente un resultado.</p>
                @if($errorLectura)<div class="rm-alert rm-alert-danger" role="alert">No se pudo consultar la historia. <button type="button" wire:click="reintentar" class="rm-btn rm-btn-secondary">Reintentar consulta</button></div>
                @elseif($lectura)
                    @if($lectura['evaluacion'])<div class="rm-expert-source-detail"><strong>{{ $lectura['evaluacion']['fecha'] }} · {{ $lectura['evaluacion']['version'] }}</strong><p>{{ $lectura['evaluacion']['estado'] }}</p></div>@endif
                    <div class="rm-expert-areas">@foreach($lectura['perfil'] as $area)<article><i class="ph ph-brain" aria-hidden="true"></i><h4>{{ $area['nombre'] }}</h4><p>{{ $area['estado'] }}</p></article>@endforeach</div>
                    @forelse($lectura['historial'] as $historica)<details class="rm-expert-source-detail"><summary>{{ $historica['fecha'] }} · {{ $historica['version'] ?? 'Versión no disponible' }}</summary>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $historica])</details>@empty<x-ui.empty-state compact title="Sin evaluación histórica publicable" description="Las fuentes y observaciones disponibles conservan su propio alcance. No se muestra una conclusión clínica que no esté conservada." />@endforelse
                    {{ $lectura['historial']->links() }}
                @endif
            </section>
            <section x-show="pestana === 'observaciones'" x-cloak class="rm-card rm-expert-admin-residents__panel" aria-label="Observaciones del expediente">
                <h3>Registros cognitivos disponibles</h3><p>Observaciones registradas, con fecha y autor cuando constan. No se convierten en puntuaciones instrumentales.</p>
                @if($errorLectura)<p role="alert">No se pudieron consultar las observaciones. Reintente la lectura.</p>
                @else
                    @forelse($lectura['registros_cognitivos'] ?? [] as $registro)<details class="rm-expert-source-detail"><summary>{{ $registro['fecha'] ?? 'Fecha no disponible' }} · {{ $registro['autor'] ?? 'Registro cognitivo' }}</summary>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $registro])</details>@empty<x-ui.empty-state compact title="Sin observaciones disponibles en esta lectura" description="Esta pestaña solo presenta las observaciones devueltas por la consulta autorizada." />@endforelse
                @endif
            </section>
        @endif
    </div>
</div>
