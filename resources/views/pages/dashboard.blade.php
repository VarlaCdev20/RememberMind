<x-sistema-layout>
    <div class="space-y-6">
        <div><p class="text-sm font-bold text-boton-acento">BDD Operativa V2</p><h1 class="text-3xl font-black">Panel institucional</h1></div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($resumen as $nombre => $valor)
                <article class="rounded-2xl border border-borde-suave bg-fondo-card p-5 shadow-card">
                    <p class="text-xs font-black uppercase tracking-wider text-meta">{{ str_replace('_', ' ', $nombre) }}</p>
                    <p class="mt-2 text-3xl font-black text-boton-acento">{{ $valor }}</p>
                </article>
            @endforeach
        </div>
    </div>
</x-sistema-layout>
