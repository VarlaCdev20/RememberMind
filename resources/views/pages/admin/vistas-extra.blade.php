<x-sistema-layout>
    @php
        $grupos = [
            'Pantallas duplicadas de pacientes' => [
                ['Expediente administrativo anterior', 'admin.adultos-mayores.index', 'Conservar hasta validar que la Ficha 360 cubra identificación, documentos y reportes.'],
                ['Selector general de Salud', 'admin.salud-seguimiento.index', 'Conservar como selector; unificar después sus paneles individuales con Ficha 360.'],
                ['Ficha clínica médica alternativa', 'admin.medico.pacientes.observacion', 'Duplica navegación clínica de la Ficha 360.'],
            ],
            'Pantallas provisionales' => [
                ['Visitas familiares', 'admin.familia-social.visitas', 'Vista base sin flujo ni persistencia.'],
                ['Ficha social', 'admin.familia-social.ficha-social', 'Vista base sin flujo ni persistencia.'],
                ['Reportes de voluntariado', 'admin.voluntariado.reportes.index', 'Reutiliza el resumen; no es un reporte independiente.'],
                ['Reportes de Enfermería', 'admin.enfermeria.reportes', 'Reutiliza el dashboard de turno.'],
            ],
            'Portales ocultos o pendientes' => [
                ['Fisioterapia', 'admin.fisioterapia.dashboard', 'Rutas existentes que apuntan al dashboard genérico.'],
                ['Portal del voluntario', 'admin.voluntario.dashboard', 'Rutas existentes que apuntan al dashboard genérico.'],
                ['Portal familiar', 'admin.familiar.dashboard', 'Rutas existentes que apuntan al dashboard genérico.'],
            ],
        ];
    @endphp

    <div class="space-y-6">
        <x-ui.page-header titulo="Vistas extra" subtitulo="Inventario exclusivo de Superadministración. Estas vistas no se eliminan hasta que usted revise y decida." icono="ph-stack-simple" />

        @foreach($grupos as $titulo => $vistas)
            <section class="rm-card overflow-hidden">
                <header class="border-b border-borde bg-fondo-hover px-5 py-4">
                    <h2 class="text-sm font-black uppercase tracking-wider text-titulo">{{ $titulo }}</h2>
                </header>
                <div class="overflow-x-auto">
                    <table class="rm-table min-w-full">
                        <thead class="rm-table-header"><tr><th>Vista</th><th>Motivo de revisión</th><th class="text-right">Acción</th></tr></thead>
                        <tbody>
                            @foreach($vistas as [$nombre, $ruta, $motivo])
                                <tr class="rm-table-row">
                                    <td class="font-bold text-titulo">{{ $nombre }}</td>
                                    <td class="text-apoyo">{{ $motivo }}</td>
                                    <td class="text-right"><a class="rm-btn-secondary" href="{{ route($ruta) }}">Revisar</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach

        <div class="rounded-2xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-4 text-sm font-semibold text-parrafo">
            Ninguna vista de este listado se elimina automáticamente. La decisión queda registrada para una limpieza posterior.
        </div>
    </div>
</x-sistema-layout>
