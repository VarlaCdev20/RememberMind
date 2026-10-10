<x-sistema-layout>
    @if(isset($informacionFamiliar))
        <x-ui.page-header
            :title="$informacionFamiliar['nombres'] . ' ' . $informacionFamiliar['apellido_paterno']"
            overline="Tu familiar"
            icon="ph-user"
            :date="now()" />
    @else
    <div class="space-y-6">
        <x-ui.page-header
    :title="$residente->nombres . ' ' . $residente->apellido_paterno . ' ' . $residente->apellido_materno"
    :subtitle="'Estado: ' . $residente->estado . ' · Cama: ' . ($residente->ocupacionActiva?->cama?->codigo ?? 'Sin asignación')"
    :overline="$residente->cod_residente"
    icon="ph-user"
    :date="now()">
    <a class="rm-btn rm-btn-secondary" href="{{ route('admin.reportes.residente', $residente) }}">
        <i class="ph-bold ph-file-pdf text-base"></i>
        <span>Descargar PDF</span>
    </a>
</x-ui.page-header>
        <div class="grid gap-5 lg:grid-cols-2">
            <section class="rounded-2xl border border-borde-suave bg-fondo-card p-5"><h2 class="text-xl font-black">Contactos autorizados</h2><ul class="mt-3 space-y-2">@forelse($residente->vinculosContacto as $vinculo)<li>{{ $vinculo->contacto->nombres }} {{ $vinculo->contacto->apellido_paterno }} — {{ $vinculo->parentesco }} @if($vinculo->autoriza_informacion)<span class="text-estado-exito">Autorizado</span>@endif</li>@empty<li class="text-meta">Sin contactos.</li>@endforelse</ul></section>
            <section class="rounded-2xl border border-borde-suave bg-fondo-card p-5"><h2 class="text-xl font-black">Atenciones</h2><ul class="mt-3 space-y-2">@forelse($residente->atenciones as $atencion)<li>{{ $atencion->fecha_hora?->format('d/m/Y H:i') }} — {{ $atencion->tipo_atencion }} ({{ $atencion->estado }})</li>@empty<li class="text-meta">Sin atenciones.</li>@endforelse</ul></section>
            <section class="rounded-2xl border border-borde-suave bg-fondo-card p-5"><h2 class="text-xl font-black">Prescripciones</h2><ul class="mt-3 space-y-2">@forelse($residente->prescripciones as $prescripcion)<li>{{ $prescripcion->medicamento->nombre_generico }} — {{ $prescripcion->estado }}</li>@empty<li class="text-meta">Sin prescripciones.</li>@endforelse</ul></section>
            <section class="rounded-2xl border border-borde-suave bg-fondo-card p-5"><h2 class="text-xl font-black">Acciones</h2><div class="mt-3 flex flex-wrap gap-2"><a class="rm-btn-secondary" href="{{ route('admin.expediente.index', $residente) }}">Expediente JSON</a><a class="rm-btn-secondary" href="{{ route('admin.reportes.residente', $residente) }}">Descargar PDF</a></div></section>
        </div>
        <livewire:residentes.expediente-panel :residente="$residente" />
    </div>
    @endif
</x-sistema-layout>
