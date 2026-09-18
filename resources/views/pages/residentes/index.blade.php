<x-sistema-layout>
    <div class="space-y-5">
        <div><p class="text-sm font-bold text-boton-acento">Entidad central</p><h1 class="text-3xl font-black">Residentes</h1><p class="text-meta">Los residentes se crean exclusivamente mediante admisión formal.</p></div>
        <form method="GET" class="flex gap-2"><input class="rm-input" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o documento"><button class="rm-btn-secondary">Buscar</button></form>
        <div class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-card">
            <table class="w-full text-left text-sm"><thead class="bg-fondo-cardSuave"><tr><th class="p-3">Residente</th><th class="p-3">Documento</th><th class="p-3">Estado</th><th class="p-3">Ubicación</th></tr></thead><tbody>
            @forelse($residentes as $residente)<tr class="border-t border-borde-suave"><td class="p-3 font-bold"><a class="text-boton-acento" href="{{ route('admin.residentes.show', $residente) }}">{{ $residente->nombres }} {{ $residente->apellido_paterno }} {{ $residente->apellido_materno }}</a></td><td class="p-3">{{ $residente->numero_documento ?: '—' }}</td><td class="p-3">{{ $residente->estado }}</td><td class="p-3">{{ $residente->ocupacionActiva?->cama?->habitacion?->codigo ?? 'Sin cama activa' }}</td></tr>@empty<tr><td class="p-6 text-center text-meta" colspan="4">No hay residentes.</td></tr>@endforelse
            </tbody></table>
        </div>
        {{ $residentes->links() }}
    </div>
</x-sistema-layout>
