<x-sistema-layout>
    <div class="space-y-5">
        <div><p class="text-sm font-bold text-boton-acento">Flujo institucional</p><h1 class="text-3xl font-black">Preadmisiones</h1><p class="text-meta">Aprobar no crea un residente; la creación ocurre únicamente al formalizar la admisión.</p></div>
        <div class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-card"><table class="w-full text-left text-sm"><thead class="bg-fondo-cardSuave"><tr><th class="p-3">Postulante</th><th class="p-3">Solicitud</th><th class="p-3">Prioridad</th><th class="p-3">Estado</th></tr></thead><tbody>@forelse($preadmisiones as $preadmision)<tr class="border-t border-borde-suave"><td class="p-3 font-bold">{{ $preadmision->nombres }} {{ $preadmision->apellido_paterno }}</td><td class="p-3">{{ $preadmision->fecha_solicitud?->format('d/m/Y H:i') }}</td><td class="p-3">{{ $preadmision->prioridad ?: '—' }}</td><td class="p-3">{{ $preadmision->estado }}</td></tr>@empty<tr><td colspan="4" class="p-6 text-center text-meta">Sin solicitudes.</td></tr>@endforelse</tbody></table></div>
        {{ $preadmisiones->links() }}
        <livewire:admisiones.preadmisiones-panel />
    </div>
</x-sistema-layout>
