<section class="rm-explorador" aria-labelledby="explorador-titulo" x-data="{ vista: 'tarjetas' }">
    <header class="rm-explorador__cabecera">
        <div><p class="rm-explorador__eyebrow">Preparar una valoración</p><h2 id="explorador-titulo">Explorador de instrumentos</h2><p>Consulta qué está disponible y qué requiere revisión antes de registrar.</p></div>
        <x-ui.avatar-guia />
    </header>
    @if($codArea === 'ARE_COG')
        <div class="rm-explorador__bibliotecas" role="group" aria-label="Fuente de instrumentos">
            <button type="button" wire:click="$set('biblioteca', 'catalogo')" class="rm-btn rm-btn-secondary" aria-pressed="{{ $biblioteca === 'catalogo' ? 'true' : 'false' }}"><i class="ph-bold ph-clipboard-text" aria-hidden="true"></i> Catálogo institucional <span>{{ $todasTarjetas->where('estado', '!=', 'CANDIDATO_INVESTIGADO')->count() }}</span></button>
            <button type="button" wire:click="$set('biblioteca', 'candidatos')" class="rm-btn rm-btn-secondary" aria-pressed="{{ $biblioteca === 'candidatos' ? 'true' : 'false' }}"><i class="ph-bold ph-books" aria-hidden="true"></i> Biblioteca de investigación <span>{{ $todasTarjetas->where('estado', 'CANDIDATO_INVESTIGADO')->count() }}</span></button>
        </div>
        @if($biblioteca === 'candidatos')
            <p class="rm-explorador__nota" role="note"><i class="ph-bold ph-info" aria-hidden="true"></i>Los métodos investigados son referencias documentales. No son instrumentos habilitados ni autorizan aplicación, licencia o interpretación clínica.</p>
        @else
            <p class="rm-explorador__dominio-ayuda">Los dominios se muestran solo cuando las preguntas vigentes los identifican. No se deducen del nombre del instrumento.</p>
        @endif
            <div class="rm-explorador__dominios" role="group" aria-label="Dominios cognitivos documentados">
                <button type="button" wire:click="$set('dominio', '')" aria-pressed="{{ $dominio === '' ? 'true' : 'false' }}">Todos los dominios</button>
                @foreach(\App\Modulos\Valoraciones\Aplicacion\Consultas\ExploradorInstrumentos::DOMINIOS as $codigo => $nombre)
                    <button type="button" wire:click="$set('dominio', '{{ $codigo }}')" aria-pressed="{{ $dominio === $codigo ? 'true' : 'false' }}">{{ $nombre }}</button>
                @endforeach
            </div>
    @endif
    <div class="rm-explorador__controles">
        <x-ui.field for="instrumento-busqueda" label="Buscar por nombre, código o finalidad">
            <input id="instrumento-busqueda" class="rm-input" type="search" wire:model.live.debounce.300ms="buscarInstrumento" maxlength="120" placeholder="Por ejemplo: memoria, observación…" autocomplete="off">
        </x-ui.field>
        <x-ui.field for="instrumento-tipo" label="Tipo">
            <x-ui.selector id="instrumento-tipo" label="Tipo de instrumento" wire:model.live="tipoInstrumento"><option value="">Todos</option>@foreach($tiposExplorador as $tipo)<option value="{{ $tipo }}">{{ str_replace('_', ' ', $tipo) }}</option>@endforeach</x-ui.selector>
        </x-ui.field>
        <x-ui.field for="instrumento-estado" label="Disponibilidad">
            <x-ui.selector id="instrumento-estado" label="Disponibilidad" wire:model.live="estadoInstrumento"><option value="">Todas</option>@if($biblioteca === 'catalogo')<option value="HABILITADO_PARA_CAPTURA">Con preguntas vigentes</option><option value="INACTIVO_HISTORICO">Histórico · inactivo</option><option value="SIN_PREGUNTAS_VIGENTES">Sin preguntas vigentes</option>@else<option value="CANDIDATO_INVESTIGADO">Candidato investigado</option>@endif</x-ui.selector>
        </x-ui.field>
        <x-ui.field for="instrumento-modalidad" label="Modalidad">
            <x-ui.selector id="instrumento-modalidad" label="Modalidad" wire:model.live="modalidadInstrumento"><option value="">Todas</option>@foreach($modalidadesExplorador as $modalidad)<option value="{{ $modalidad }}">{{ $modalidad }}</option>@endforeach</x-ui.selector>
        </x-ui.field>
    </div>
    <div class="rm-explorador__resultados">
        <p role="status" aria-live="polite">{{ $tarjetas->count() }} {{ $tarjetas->count() === 1 ? 'resultado' : 'resultados' }} · {{ $biblioteca === 'catalogo' ? 'Catálogo institucional' : 'Consulta documental' }}</p>
        <div class="rm-explorador__vista" role="group" aria-label="Presentación del explorador"><button type="button" @click="vista = 'tarjetas'" :aria-pressed="vista === 'tarjetas'" aria-label="Ver tarjetas"><i class="ph-bold ph-squares-four" aria-hidden="true"></i></button><button type="button" @click="vista = 'lista'" :aria-pressed="vista === 'lista'" aria-label="Ver lista"><i class="ph-bold ph-list-bullets" aria-hidden="true"></i></button></div>
    </div>
    <p wire:loading.delay role="status" class="rm-explorador__cargando">Actualizando instrumentos…</p>
    <div class="rm-explorador__grid" :class="{ 'rm-explorador__grid--lista': vista === 'lista' }" wire:loading.class="rm-explorador--cargando">
        @forelse($tarjetas as $tarjeta)
            <article class="rm-instrumento" wire:key="instrumento-{{ $tarjeta['id'] }}">
                <div class="rm-instrumento__identidad"><span class="rm-instrumento__icono"><i class="ph-bold {{ $tarjeta['tipo'] === 'OBSERVACIONAL' ? 'ph-eye' : ($tarjeta['estado'] === 'CANDIDATO_INVESTIGADO' ? 'ph-book-bookmark' : 'ph-clipboard-text') }}" aria-hidden="true"></i></span><div><p class="rm-instrumento__codigo">{{ $tarjeta['codigo'] }}</p><h3>{{ $tarjeta['nombre'] }}</h3></div><button type="button" class="rm-btn-icon" wire:click="abrirGuia(@js($tarjeta['id']))" wire:loading.attr="disabled" aria-label="Explicación del instrumento {{ $tarjeta['nombre'] }}"><i class="ph-bold ph-question" aria-hidden="true"></i></button></div>
                <div class="rm-instrumento__etiquetas"><span class="rm-badge">{{ $tarjeta['modalidad'] }}</span><span class="rm-badge">{{ match($tarjeta['estado']) { 'CANDIDATO_INVESTIGADO' => 'Candidato investigado', 'INACTIVO_HISTORICO' => 'Histórico · inactivo', 'SIN_PREGUNTAS_VIGENTES' => 'Sin preguntas vigentes', default => $puedeCapturar ? 'Disponible para preparar captura' : 'Solo consulta' } }}</span></div>
                @if($tarjeta['dominios'])<p class="rm-instrumento__dominios">{{ implode(' · ', array_map(static fn ($d) => \App\Modulos\Valoraciones\Aplicacion\Consultas\ExploradorInstrumentos::DOMINIOS[$d], $tarjeta['dominios'])) }}</p>@endif
                <p class="rm-instrumento__descripcion">{{ $tarjeta['descripcion'] }}</p>
                <dl class="rm-instrumento__meta"><div><dt>Versión</dt><dd>{{ $tarjeta['version'] }}</dd></div><div><dt>Aporte</dt><dd>{{ $tarjeta['aporte'] }}</dd></div><div><dt>Catálogo</dt><dd>{{ $tarjeta['estado_catalogo'] }}</dd></div></dl>
                <footer class="rm-instrumento__acciones"><button type="button" wire:click="abrirGuia(@js($tarjeta['id']))" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary"><i class="ph-bold ph-book-open" aria-hidden="true"></i> Ver guía<span class="sr-only"> de {{ $tarjeta['nombre'] }}</span></button>
                    @if($puedeCapturar && $tarjeta['estado'] === 'HABILITADO_PARA_CAPTURA')<button type="button" wire:click="nuevaEvaluacion(null, @js($tarjeta['cod_instrumento']))" wire:loading.attr="disabled" class="rm-btn rm-btn-primary">Registrar aplicación<span class="sr-only"> de {{ $tarjeta['nombre'] }}</span></button>@endif
                </footer>
            </article>
        @empty
            <div class="rm-explorador__vacio"><i class="ph-bold {{ $todasTarjetas->isEmpty() ? 'ph-chat-centered-text' : 'ph-magnifying-glass' }}" aria-hidden="true"></i>
                @if($buscarInstrumento || $dominio || $tipoInstrumento || $estadoInstrumento || $modalidadInstrumento)<h3>Sin resultados con estos filtros</h3><p>Amplía la búsqueda para revisar los instrumentos disponibles.</p><button type="button" wire:click="limpiarExplorador" class="rm-btn rm-btn-secondary">Limpiar filtros</button>
                @else<h3>{{ $codArea === 'ARE_AFE' ? 'La entrevista y la valoración profesional son el punto de partida' : ($codArea === 'ARE_SOC' ? 'Conoce el entorno y las fuentes de apoyo' : 'Sin instrumentos asociados en el catálogo actual') }}</h3><p>{{ $codArea === 'ARE_AFE' ? 'Consulta el estado afectivo, conducta, conclusión y seguimiento desde la ficha psicológica del residente.' : ($codArea === 'ARE_SOC' ? 'Consulta el contexto, la relación del informante y la red de apoyo autorizada desde la ficha. No se generan tests sociales.' : 'Puedes consultar los antecedentes y la valoración profesional existente del residente.') }}</p>@endif
            </div>
        @endforelse
    </div>
    @if(!$puedeCapturar && $instrumentos->isNotEmpty())<p class="rm-explorador__nota" role="note">Consulta disponible. Para registrar se requiere permiso de captura, competencia profesional y un área activa asignada en la jornada.</p>@endif
</section>
