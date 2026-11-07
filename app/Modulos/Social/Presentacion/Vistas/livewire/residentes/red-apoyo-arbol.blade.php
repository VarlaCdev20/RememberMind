<section @class(['rm-support-network', 'rm-support-network--single' => $vinculos->count() === 1]) aria-label="Red de apoyo del residente seleccionado">
    <div class="rm-support-network__resident">
        <span class="rm-support-network__resident-icon" aria-hidden="true"><i class="ph-bold ph-user-circle"></i></span>
        <div><span class="rm-support-network__eyebrow">RESIDENTE</span><h2>{{ $residente->nombre_completo }}</h2><p>{{ $vinculos->count() }} {{ $vinculos->count() === 1 ? 'vínculo registrado' : 'vínculos registrados' }}</p></div>
    </div>
    @if($vinculos->isEmpty())
        <x-ui.empty-state title="Sin vínculos registrados" description="Registra un contacto o vincula una persona existente para completar su red de apoyo." icon="ph-users-three" />
    @else
        <ul class="rm-support-network__branches" aria-label="Contactos vinculados al residente">
            @foreach($vinculos->sortBy(fn ($v) => ($v->estado === 'ACTIVO' ? '0' : '1').($v->responsable_principal ? '0' : '1').($v->contacto_emergencia ? '0' : '1').$v->contacto->nombre_completo) as $vinculo)
                <li @class(['rm-support-network__branch', 'rm-support-network__branch--inactive' => $vinculo->estado !== 'ACTIVO']) wire:key="familia-nodo-{{ $vinculo->getKey() }}" data-vinculo-nodo="{{ $vinculo->getKey() }}">
                    <article class="rm-card rm-support-contact">
                        <header class="rm-support-contact__identity">
                            <span class="rm-support-contact__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($vinculo->contacto->nombres,0,1).mb_substr($vinculo->contacto->apellido_paterno,0,1)) }}</span>
                            <div><h3>{{ $vinculo->contacto->nombre_completo }}</h3><p>{{ $vinculo->parentesco }}</p></div>
                            <span class="rm-badge rm-badge-{{ $vinculo->estado === 'ACTIVO' ? 'success' : 'neutral' }}">{{ $vinculo->estado === 'ACTIVO' ? 'Vínculo activo' : 'Vínculo inactivo' }}</span>
                        </header>
                        @if($vinculo->contacto->estado !== 'ACTIVO')<p class="rm-help">Persona de contacto inactiva</p>@endif
                        <div class="rm-support-contact__roles">
                            @if($vinculo->responsable_principal)<span class="rm-badge rm-badge-info"><i class="ph-bold ph-user-check" aria-hidden="true"></i>Responsable principal</span>@endif
                            @if($vinculo->contacto_emergencia)<span class="rm-badge rm-badge-neutral"><i class="ph-bold ph-phone-call" aria-hidden="true"></i>Contacto de emergencia</span>@endif
                        </div>
                        <div class="rm-support-contact__channels">
                            <p><i class="ph ph-phone" aria-hidden="true"></i>{{ $vinculo->contacto->celular ?: ($vinculo->contacto->telefono ?: 'Teléfono no registrado') }}</p>
                            @can('contactos.ver')<p><i class="ph ph-envelope-simple" aria-hidden="true"></i>{{ $vinculo->contacto->correo ?: 'Correo no registrado' }}</p>@endcan
                        </div>
                        <dl class="rm-support-contact__permissions"><div><dt>Información</dt><dd>{{ $vinculo->autoriza_informacion ? 'Autorización registrada' : 'No acreditada' }}</dd></div><div><dt>Salidas</dt><dd>{{ $vinculo->autoriza_salida ? 'Autorización registrada' : 'No acreditada' }}</dd></div></dl>
                        @if(auth()->user()->can('contactos.gestionar') || auth()->user()->can('residentes_contactos.gestionar'))
                            <details class="rm-support-contact__manage"><summary>Gestionar contacto y vínculo <i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary>@include('social::livewire.residentes.red-apoyo-acciones-vinculo')</details>
                        @endif
                    </article>
                </li>
            @endforeach
        </ul>
        <p class="rm-help rm-support-network__help">Cada línea representa el vínculo registrado con este residente. Las autorizaciones de información y salidas conservan sus controles propios.</p>
    @endif
</section>
