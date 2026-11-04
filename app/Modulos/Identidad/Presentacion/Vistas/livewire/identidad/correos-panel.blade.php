<div class="rm-page-layout space-y-4 min-w-0" data-correo-historial>
    <x-ui.collection-header title="Historial de correos" subtitle="Consulta tus envíos de fichas y paquetes documentales." icon="ph-envelope" eyebrow="Administración">
        <x-slot:actions>
            <button type="button" wire:click="$refresh" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary"><i class="ph ph-arrows-clockwise" aria-hidden="true"></i> Actualizar</button>
            @can('usuarios.ver')<a class="rm-btn rm-btn-secondary" href="{{ route('admin.usuarios.index') }}">Usuarios</a>@endcan
        </x-slot:actions>
    </x-ui.collection-header>

    <section class="rm-card p-4 space-y-2" aria-label="Alcance del historial">
        @if($nombreUsuario)
            <p class="font-semibold break-words">Envíos para {{ $nombreUsuario }}</p>
            <a class="rm-btn rm-btn-ghost" href="{{ route('admin.usuarios.correos.index') }}">Ver todos mis envíos</a>
        @endif
        <p class="text-sm text-rm-secondary">Solo se muestran las solicitudes que realizaste con esta cuenta y que tus permisos actuales permiten consultar.</p>
        <p class="text-sm"><i class="ph ph-info" aria-hidden="true"></i> <strong>Aceptado por el servidor</strong> significa que el servicio de correo lo recibió para su envío. No confirma que la persona lo haya recibido o leído.</p>
    </section>

    <form wire:submit="aplicarFiltros" class="rm-card p-4 space-y-3" aria-label="Filtrar correos">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <div class="min-w-0"><label for="correo-busqueda" class="block text-sm font-semibold mb-1">Nombre del destinatario</label><input id="correo-busqueda" class="rm-input w-full" wire:model="busqueda" maxlength="100" type="search" placeholder="Nombre o apellido">@error('busqueda')<p role="alert">{{ $message }}</p>@enderror</div>
            <div class="min-w-0"><label for="correo-tipo" class="block text-sm font-semibold mb-1">Documento</label><select id="correo-tipo" class="rm-select w-full !leading-normal" wire:model="tipo"><option value="">Todos los documentos</option><option value="ficha">Ficha institucional</option><option value="paquete">Paquete documental</option></select>@error('tipo')<p role="alert">{{ $message }}</p>@enderror</div>
            <div class="min-w-0"><label for="correo-estado" class="block text-sm font-semibold mb-1">Estado</label><select id="correo-estado" class="rm-select w-full !leading-normal" wire:model="estado"><option value="">Todos los estados</option>@foreach($estados as $codigo => $texto)<option value="{{ $codigo }}">{{ $texto[0] }}{{ $codigo === 'PREPARANDO' ? ' · preparando documento' : ($codigo === 'ENVIANDO' ? ' · contactando servidor' : '') }}</option>@endforeach</select>@error('estado')<p role="alert">{{ $message }}</p>@enderror</div>
            <div class="min-w-0"><label for="correo-desde" class="block text-sm font-semibold mb-1">Actualizado desde</label><input id="correo-desde" class="rm-input w-full" type="date" wire:model="desde">@error('desde')<p role="alert">{{ $message }}</p>@enderror</div>
            <div class="min-w-0"><label for="correo-hasta" class="block text-sm font-semibold mb-1">Actualizado hasta</label><input id="correo-hasta" class="rm-input w-full" type="date" wire:model="hasta">@error('hasta')<p role="alert">{{ $message }}</p>@enderror</div>
        </div>
        <div class="flex flex-wrap gap-2"><button type="submit" class="rm-btn rm-btn-primary" wire:loading.attr="disabled">Aplicar filtros</button><button type="button" class="rm-btn rm-btn-secondary" wire:click="limpiarFiltros" wire:loading.attr="disabled">Limpiar filtros</button></div>
    </form>

    <div wire:loading.delay role="status" class="text-sm text-rm-secondary">Actualizando historial…</div>
    <section class="rm-card p-4 min-w-0" aria-labelledby="correo-listado">
        <h2 id="correo-listado" class="font-semibold mb-3">Solicitudes de envío <span class="text-rm-secondary">({{ $envios?->total() ?? 0 }})</span></h2>
        @if($envios === null)
            <p role="alert">Revisa los filtros indicados y vuelve a aplicarlos.</p>
        @elseif($filas->isEmpty())
            <div class="py-6 text-center space-y-2"><i class="ph ph-envelope-open text-2xl" aria-hidden="true"></i><p class="font-semibold">No hay solicitudes para mostrar</p><p class="text-sm text-rm-secondary">Si aplicaste filtros, prueba a limpiarlos. Los envíos anteriores a la incorporación de este seguimiento no aparecen aquí.</p></div>
        @else
            <div class="hidden lg:block">
                <table class="w-full table-fixed text-sm">
                    <caption class="sr-only">Tus solicitudes: documento, destinatario, última actualización, estado y detalle.</caption>
                    <thead class="text-left border-b border-borde"><tr><th scope="col" class="p-2">Documento y destinatario</th><th scope="col" class="p-2">Última actualización</th><th scope="col" class="p-2">Estado</th><th scope="col" class="p-2 w-28">Detalle</th></tr></thead>
                    <tbody>@foreach($filas as $fila)<tr class="border-b border-borde" wire:key="correo-tabla-{{ $fila['solicitud'] }}"><td class="p-2 break-words"><strong>{{ $fila['documento'] }}</strong><span class="block text-rm-secondary">{{ $fila['destinatario'] }}</span></td><td class="p-2">{{ $fila['fecha'] }}</td><td class="p-2"><span class="rm-badge rm-badge-{{ $fila['tono'] }} !whitespace-normal">{{ $fila['titulo'] }}</span></td><td class="p-2"><button class="rm-btn rm-btn-secondary" wire:click="abrirDetalle('{{ $fila['solicitud'] }}')" wire:loading.attr="disabled" aria-label="Ver detalle de {{ $fila['documento'] }} para {{ $fila['destinatario'] }}">Ver detalle</button></td></tr>@endforeach</tbody>
                </table>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:hidden">
                @foreach($filas as $fila)
                    <article class="rounded-lg border border-borde p-3 space-y-2 min-w-0" wire:key="correo-tarjeta-{{ $fila['solicitud'] }}">
                        <h3 class="font-semibold">{{ $fila['documento'] }}</h3><p class="break-words">{{ $fila['destinatario'] }}</p>
                        <p class="text-sm text-rm-secondary">Última actualización: {{ $fila['fecha'] }}</p>
                        <span class="rm-badge rm-badge-{{ $fila['tono'] }} !whitespace-normal">{{ $fila['titulo'] }}</span>
                        <div><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirDetalle('{{ $fila['solicitud'] }}')" wire:loading.attr="disabled" aria-label="Ver detalle de {{ $fila['documento'] }} para {{ $fila['destinatario'] }}">Ver detalle</button></div>
                    </article>
                @endforeach
            </div>
            <div class="mt-4">{{ $envios->links() }}</div>
        @endif
    </section>

    <x-ui.modal-livewire id="correo-detalle" title="Detalle del envío" subtitle="Estados registrados por el sistema, en orden de ocurrencia." wire:model="mostrarDetalle" closeMethod="cerrarDetalle" maxWidth="md" icon="ph-envelope">
        @if($detalle)
            <div class="space-y-4">
                @if($confirmacion)<p class="rm-notice p-3" role="status">{{ $confirmacion }}</p>@endif
                <dl class="grid gap-3 sm:grid-cols-2 text-sm"><div><dt class="text-rm-secondary">Documento</dt><dd class="font-semibold">{{ $detalle['actual']['documento'] }}</dd></div><div><dt class="text-rm-secondary">Destinatario</dt><dd class="font-semibold break-words">{{ $detalle['actual']['destinatario'] }}</dd></div><div class="sm:col-span-2"><dt class="text-rm-secondary">Solicitado por</dt><dd class="break-words">{{ $detalle['actual']['responsable'] }}</dd></div></dl>
                <div class="rounded-lg border border-borde p-3 space-y-2"><span class="rm-badge rm-badge-{{ $detalle['actual']['tono'] }} !whitespace-normal">{{ $detalle['actual']['titulo'] }}</span><p>{{ $detalle['actual']['mensaje'] }}</p></div>
                <h4 class="font-semibold">Recorrido del envío</h4>
                <ol class="space-y-3" aria-label="Historial de intentos">@foreach($detalle['eventos'] as $evento)<li class="border-l-2 border-borde pl-3"><p class="font-semibold">{{ $evento['titulo'] }} @if($evento['intento'] > 0)<span class="text-sm text-rm-secondary">· intento {{ $evento['intento'] }} de 3</span>@endif</p><p class="text-sm text-rm-secondary">{{ $evento['fecha'] }}</p>@if($evento['proximo'])<p class="text-sm">Próximo intento previsto: {{ $evento['proximo'] }}</p>@endif</li>@endforeach</ol>
                <p class="text-sm text-rm-secondary">El nombre corresponde a la cuenta actual. Este historial no conserva una copia del documento ni de la dirección de correo utilizada.</p>
                @if($detalle['actual']['estado'] === 'FALLIDO')
                    <p class="text-sm">Volver a solicitar prepara un documento nuevo con los datos y el correo actuales. Comprueba que sean correctos.</p>
                    <button type="button" class="rm-btn rm-btn-primary" wire:click="reenviar" wire:confirm="¿Solicitar otro envío de este documento? Se utilizarán los datos y el correo actuales del usuario." wire:loading.attr="disabled" wire:target="reenviar">Volver a solicitar envío</button>
                @endif
            </div>
        @endif
        <x-slot:footer><button type="button" class="rm-btn rm-btn-secondary" wire:click="$refresh" wire:loading.attr="disabled">Actualizar estado</button><button type="button" class="rm-btn rm-btn-secondary" wire:click="cerrarDetalle">Cerrar</button></x-slot:footer>
    </x-ui.modal-livewire>
</div>
