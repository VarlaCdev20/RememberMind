<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use Illuminate\Http\Request;

/** Excepción de transporte para consultas de Alertas; nunca autoriza escrituras. */
final class ConsultaAlertasPrevia
{
    private const CAMPOS = ['search', 'filtroEstado', 'filtroNivel', 'filtroOrigen', 'filtroAdulto',
        'filtroResponsabilidad', 'filtroEspecialidad', 'fechaDesde', 'fechaHasta', 'perPage', 'mostrarAnalisis', 'paginators.page'];

    private const METODOS = ['$refresh', 'limpiarFiltros', 'limpiarFiltro', 'setFiltroRapido',
        'filtrarResumen', 'filtrarEquipo', 'verPrioritarias', 'seleccionarTraza', 'verDetalle', 'verGraficos', 'verUbicacion',
        'cerrarDrawer', 'cerrarModales', 'solicitarCerrarModales', 'abrirCrear', 'abrirRegistroEspecialidad', 'gotoPage', 'nextPage', 'previousPage', 'setPage'];

    public function admite(Request $request): bool
    {
        if (! $request->isMethod('POST') || ! $request->hasHeader('X-Livewire')
            || ! str_ends_with($request->route()?->getName() ?? '', 'livewire.update')) {
            return false;
        }
        $componentes = $request->input('components');
        if (! is_array($componentes) || $componentes === [] || count($componentes) > 10) {
            return false;
        }
        foreach ($componentes as $componente) {
            if (! is_array($componente) || ! is_string($componente['snapshot'] ?? null)) {
                return false;
            }
            $snapshot = json_decode($componente['snapshot'] ?? '', true);
            if (! is_array($snapshot) || ! is_array($snapshot['memo'] ?? null)) {
                return false;
            }
            if (($snapshot['memo']['name'] ?? '') === 'alertas.comparar-roles') {
                if (! is_string($snapshot['checksum'] ?? null)
                    || ! is_array($componente['updates'] ?? []) || ! is_array($componente['calls'] ?? [])
                    || array_diff(array_keys($componente['updates'] ?? []), ['rol'])) {
                    return false;
                }
                foreach ($componente['calls'] ?? [] as $call) {
                    if (! is_array($call) || ! in_array($call['method'] ?? '', ['seleccionarRol', '$refresh'], true)) {
                        return false;
                    }
                }

                continue;
            }
            // Livewire verifica después la firma y el middleware persistente del snapshot.
            if (! is_array($snapshot) || ! is_string($snapshot['checksum'] ?? null) || ! in_array($snapshot['memo']['name'] ?? '', [
                'alertas.alertas-panel', 'app.frontend.livewire.compartido.alertas.alertas-panel',
            ], true)) {
                return false;
            }
            $updates = $componente['updates'] ?? [];
            $calls = $componente['calls'] ?? [];
            if (! is_array($updates) || ! is_array($calls) || array_diff(array_keys($updates), self::CAMPOS)) {
                return false;
            }
            foreach ($calls as $call) {
                if (! is_array($call) || ! is_array($call['params'] ?? [])) {
                    return false;
                }
                $metodo = $call['method'] ?? '';
                if ($metodo === '$toggle' && ($call['params'] ?? []) === ['mostrarAnalisis']) {
                    continue;
                }
                if ($metodo === '$set' && in_array($call['params'][0] ?? '', self::CAMPOS, true)) {
                    continue;
                }
                if (! in_array($metodo, self::METODOS, true)) {
                    return false;
                }
            }
        }

        return true;
    }
}
