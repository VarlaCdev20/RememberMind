<div class="rm-support-picker">
    <div class="rm-support-view-tools">
        <div><h2 class="rm-section-title">Selecciona un residente</h2><p class="rm-help">Abre su red para consultar los familiares y contactos registrados.</p></div>
        <a class="rm-btn rm-btn-secondary" href="{{ route('admin.residentes.index') }}"><i class="ph-bold ph-users-three" aria-hidden="true"></i>Ver todos los residentes</a>
    </div>
    @if($residentes->isEmpty())
        <x-ui.empty-state :title="trim($buscar) !== '' ? 'No hay residentes con esa búsqueda' : 'Sin residentes admitidos en esta consulta'" description="Revisa el nombre o consulta el directorio de residentes y sus estados administrativos." icon="ph-user-magnifying-glass" />
    @else
        <ul class="rm-support-picker__grid" aria-label="Residentes disponibles para consultar su red">
            @foreach($residentes as $persona)
                <li><a class="rm-card rm-support-picker__resident" data-residente-red="{{ $persona->getKey() }}" href="{{ route('admin.familia-social.red-apoyo', ['residente' => $persona->getKey()]) }}" aria-label="Ver red de apoyo de {{ $persona->nombre_completo }}">
                    <span class="rm-support-contact__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr((string)$persona->nombres,0,1).mb_substr((string)$persona->apellido_paterno,0,1)) }}</span>
                    <div><h3>{{ $persona->nombre_completo }}</h3><p>{{ $persona->vinculos_contacto_count }} {{ (int)$persona->vinculos_contacto_count === 1 ? 'vínculo registrado' : 'vínculos registrados' }}</p><span class="rm-btn rm-btn-secondary rm-support-picker__open"><i class="ph-bold ph-tree-structure" aria-hidden="true"></i>Ver red de apoyo <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></span></div>
                </a></li>
            @endforeach
        </ul>
        <p class="rm-help">{{ $residentes->count() }} coincidencias mostradas. Se muestran hasta 40 residentes admitidos; busca por nombre y apellidos para encontrar otra persona.</p>
    @endif
</div>
