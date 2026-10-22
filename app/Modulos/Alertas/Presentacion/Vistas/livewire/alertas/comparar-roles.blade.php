<div class="rm-page-layout rm-pilot-alertas rm-alert-roles" x-data @alertas-rol-seleccionado.window="if (window.matchMedia('(max-width: 1023px)').matches) { $refs.contenidoRol.focus({ preventScroll: true }); $refs.contenidoRol.scrollIntoView({ block: 'start', behavior: 'instant' }); }">
    <x-ui.page-header title="Alertas por rol y especialidad" subtitle="Explora qué necesita ver y registrar cada integrante del equipo." icon="ph-users-three" overline="Equipo y continuidad"><a href="{{ route('admin.alertas-clinicas.index') }}" class="rm-btn rm-btn-secondary"><i class="ph-bold ph-arrow-left" aria-hidden="true"></i> Volver a Alertas</a></x-ui.page-header>
    <div class="rm-alert-roles__workspace">
        <nav class="rm-alert-roles__navigation" aria-label="Rol que deseas consultar">
            <div class="rm-alert-roles__nav-heading"><h2>Elige un rol</h2><p>Su enfoque y sus campos se muestran al seleccionarlo.</p></div>
            <div class="rm-alert-roles__choices">
                @foreach($roles as $valor=>$nombre)
                    <button type="button" class="rm-alert-roles__choice" wire:click="seleccionarRol('{{ $valor }}')" wire:loading.attr="disabled" wire:target="seleccionarRol" aria-pressed="{{ $rol === $valor ? 'true' : 'false' }}" aria-controls="alertas-vista-rol">
                        <i class="ph-bold {{ $presentacionRoles[$valor][0] }}" aria-hidden="true"></i><span><strong>{{ $nombre }}</strong><small>{{ $presentacionRoles[$valor][1] }}</small></span><i class="ph-bold {{ $rol === $valor ? 'ph-check-circle' : 'ph-caret-right' }}" aria-hidden="true"></i>
                    </button>
                @endforeach
            </div>
        </nav>
        <section id="alertas-vista-rol" x-ref="contenidoRol" tabindex="-1" aria-label="Enfoque y campos del rol seleccionado" class="rm-alert-roles__content" aria-live="polite" aria-atomic="false" wire:loading.class="opacity-60" wire:target="seleccionarRol">
            <header class="rm-alert-roles__intro">
                <span class="rm-alert-roles__eyebrow"><i class="ph-bold {{ $presentacionRoles[$rol][0] }}" aria-hidden="true"></i> {{ $roles[$rol] }}</span>
                <h2>{{ $perfil['titulo'] }}</h2><p>{{ $perfil['descripcion'] }}</p>
                <p><strong>Recorrido:</strong> {{ $perfil['bandeja'] }} → {{ $perfil['seleccionar'] }}@if($formulario) → {{ $formulario['titulo'] }}@endif.</p>
                <div class="rm-alert-roles__priorities">@foreach($perfil['claves'] as $clave)<span><i class="ph-bold ph-check" aria-hidden="true"></i>{{ $clave }}</span>@endforeach</div>
            </header>
            @if($rol !== 'FAMILIAR')<x-ui.alerta-vista-profesional :vista="app(\App\Modulos\Alertas\Presentacion\Soporte\ComponerVistaCasoAlerta::class)->componer(null, $perfil['foco'])" />@endif
            <div class="rm-alert-roles__section-heading"><div><p class="rm-alert-roles__eyebrow">Qué se registra</p><h3>{{ $formulario['titulo'] ?? match($rol) { 'ENFERMEROS'=>'Intervención y seguimiento de Enfermería', 'FAMILIAR'=>'Información familiar autorizada', 'GERENTE'=>'Supervisión institucional', 'ADMINISTRADOR'=>'Aviso y coordinación operativa', default=>'Supervisión y trazabilidad' } }}</h3></div>@if($formulario)<span class="rm-alert-roles__count">{{ count($formulario['campos']) }} campos · {{ count($formulario['secciones']) }} {{ count($formulario['secciones']) === 1 ? 'sección' : 'secciones' }}</span>@endif</div>
            @if($formulario)
                @if($formulario['requiereAtencion'])<p class="rm-alert-roles__context"><i class="ph-bold ph-folder-user" aria-hidden="true"></i> Vinculado a una atención propia del residente, con fecha, autor y área de la jornada.</p>@endif
                <div class="rm-alert-roles__sections">
                    @foreach($formulario['secciones'] as $tituloSeccion=>$camposSeccion)
                        <section class="rm-alert-roles__field-group"><h4><span>{{ $loop->iteration }}</span>{{ $tituloSeccion }}</h4><ul>@foreach($camposSeccion as $campo)<li><span>{{ $formulario['campos'][$campo][0] }}</span><small>{{ match($formulario['campos'][$campo][1]) { 'booleano'=>'Sin registrar / Sí / No', 'decimal'=>'Cantidad en mL por día', 'texto'=>'Descripción profesional', default=>'Observación profesional' } }}</small></li>@endforeach</ul></section>
                    @endforeach
                </div>
                <p class="rm-alert-roles__context"><i class="ph-bold ph-path" aria-hidden="true"></i> El registro se guarda en el expediente de la especialidad y queda vinculado al historial de la alerta.</p>
            @elseif($rol === 'ENFERMEROS')
                <dl class="rm-alert-case__facts"><div><dt>Contexto del cuidado</dt><dd>Residente asignado, momento del hecho y personal activo.</dd></div><div><dt>Nota de intervención</dt><dd>Observación, actuación realizada, respuesta, comunicación y pendientes.</dd></div><div><dt>Seguimiento</dt><dd>Continuidad de la atención en curso y responsabilidad del siguiente paso.</dd></div><div><dt>Cierre</dt><dd>Justificación del cierre con historial conservado.</dd></div></dl>
            @elseif($rol === 'FAMILIAR')
                <x-ui.callout variant="info" title="Portal del residente vinculado">No accede al centro de alertas clínicas ni a sus formularios. La familia consulta la información compartida para sus residentes autorizados. La publicación automática necesita un contrato institucional aprobado.</x-ui.callout>
            @elseif($rol === 'GERENTE')
                <dl class="rm-alert-case__facts"><div><dt>Reconocimiento</dt><dd>Constancia de revisión del aviso.</dd></div><div><dt>Responsable del seguimiento</dt><dd>Asignación conforme a permisos y acceso al residente.</dd></div><div><dt>Supervisión</dt><dd>Prioridades, pendientes e historial de gestión del equipo.</dd></div></dl>
            @else
                <dl class="rm-alert-case__facts"><div><dt>Contexto del aviso</dt><dd>Residente, tipo, motivo y origen declarado.</dd></div><div><dt>Prioridad y situación</dt><dd>Prioridad registrada y estado del seguimiento.</dd></div><div><dt>Coordinación</dt><dd>Responsable y nota de seguimiento operativo.</dd></div><div><dt>Cierre operativo</dt><dd>Justificación y trazabilidad de la gestión.</dd></div></dl>
            @endif
            <footer class="rm-alert-roles__footer"><p><i class="ph-bold ph-eye" aria-hidden="true"></i> Comparación sin datos de residentes. Los registros clínicos requieren permiso y competencia profesional.</p>@if(auth()->user()->hasRole('SUPERADMINISTRADOR'))<form method="POST" action="{{ route('role-preview.store') }}">@csrf<input type="hidden" name="role" value="{{ $rol }}"><input type="hidden" name="destino" value="alertas"><button type="submit" class="rm-btn rm-btn-primary"><i class="ph-bold ph-arrow-square-out" aria-hidden="true"></i>{{ $rol === 'FAMILIAR' ? 'Ver portal familiar' : 'Ver bandeja de este rol' }}</button><small class="rm-help">Previsualización de solo lectura</small></form>@endif</footer>
        </section>
    </div>
</div>
