<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Models\Residente;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Database\Eloquent\Collection;

/** Una frontera de autorización y preparación para ambos paneles laterales. */
final class ConsultarPanelResidenteAlerta
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function consultar(?User $actor, string $codigo, string $panel): Residente
    {
        $actor = $actor ? User::query()->find($actor->getKey()) : null;
        abort_unless($actor?->estado === 'ACTIVO'
            && $actor->canAny(['alertas.ver', 'alertas.gestionar']) && ! $actor->hasRole('FAMILIAR'), 403);
        if ($actor->hasRole('ENFERMEROS')) {
            $this->turnos->autorizarConsultaResidente($codigo, $actor);
        } elseif (! $actor->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])) {
            abort_unless($this->turnos->obtenerPacientesAsignadosQuery($actor)->whereKey($codigo)->exists(), 403);
        }
        abort_unless(in_array($panel, ['graficos', 'ubicacion'], true), 404);
        if ($panel === 'graficos') {
            abort_unless($actor->can('signos_vitales.ver'), 403);
        }
        $relaciones = $panel === 'graficos' ? [
            'cama.habitacion',
            'signosVitales' => fn ($q) => $q->with('registradoPor')->where('estado', '!=', 'ANULADO')->latest('fecha_hora')->take(10),
        ] : [
            'cama.habitacion',
        ];

        $residente = Residente::with($relaciones)->findOrFail($codigo);
        if ($panel === 'graficos') {
            foreach (['alergias' => 'alergias.ver', 'diagnosticos' => 'diagnosticos.ver',
                'planCuidadoActivo' => 'planes_cuidado.ver', 'valoracionesEnfermeria' => 'valoracion_enfermeria.ver',
                'seguimientosDiarios' => 'atenciones.ver'] as $relacion => $permiso) {
                if ($actor->can($permiso)) {
                    $residente->load($relacion);
                } else {
                    $residente->setRelation($relacion, $relacion === 'planCuidadoActivo' ? null : new Collection);
                }
            }
            // Preparar autoría sin consultas implícitas en la vista.
            $residente->valoracionesEnfermeria->load('personal.usuario.personal');
            $residente->seguimientosDiarios->load('personal.usuario.personal');
        }

        return $residente;
    }
}
