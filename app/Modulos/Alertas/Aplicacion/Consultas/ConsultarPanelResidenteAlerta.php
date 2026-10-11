<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Models\Residente;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;

/** Una frontera de autorización y preparación para ambos paneles laterales. */
final class ConsultarPanelResidenteAlerta
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function consultar(?User $actor, string $codigo, string $panel): Residente
    {
        $actor = $actor ? User::query()->find($actor->getKey()) : null;
        abort_unless($actor?->estado === 'ACTIVO'
            && $actor->canAny(['alertas.ver', 'alertas.gestionar']), 403);
        if ($actor->hasRole('ENFERMEROS')) {
            $this->turnos->autorizarAccionPaciente($codigo, $actor);
        } elseif (! $actor->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])) {
            abort_unless($this->turnos->obtenerPacientesAsignadosQuery($actor)->whereKey($codigo)->exists(), 403);
        }
        abort_unless(in_array($panel, ['graficos', 'ubicacion'], true), 404);
        $relaciones = $panel === 'graficos' ? [
            'cama.habitacion',
            'alergias' => fn ($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO']),
            'diagnosticos' => fn ($q) => $q->whereIn('estado', ['ACTIVO', 'CONFIRMADO']),
            'planCuidadoActivo',
            'valoracionesEnfermeria.personal.usuario.personal',
            'seguimientosDiarios.personal.usuario.personal',
            'signosVitales' => fn ($q) => $q->with('registradoPor')->where('estado', '!=', 'ANULADO')->latest('fecha_hora')->take(10),
        ] : [
            'cama.habitacion',
            'alertas' => fn ($q) => $q->latest('fecha_hora')->take(5),
        ];

        return Residente::with($relaciones)->findOrFail($codigo);
    }
}
