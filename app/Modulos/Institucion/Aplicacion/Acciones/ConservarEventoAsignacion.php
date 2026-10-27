<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\AsignacionPersonal;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ConservarEventoAsignacion
{
    public function exigir(): void
    {
        abort_unless($this->disponible(), 409, 'La conservación nominal de cambios de asignación todavía no está instalada. La consulta y las altas de ocurrencias permanecen disponibles.');
    }

    public function disponible(): bool
    {
        return Schema::hasTable('eventos_asignacion_personal');
    }

    public function conservar(AsignacionPersonal $asignacion, User $actor, string $tipo, ?string $estadoAnterior, CarbonInterface $efecto, string $motivo): void
    {
        if (! $this->disponible()) {
            return;
        }
        $previo = DB::table('eventos_asignacion_personal')->where('cod_asignacion_personal', $asignacion->getKey())->orderByDesc('numero_evento')->first();
        DB::table('eventos_asignacion_personal')->insert([
            'cod_evento_asignacion' => app(CodigosEntidad::class)->siguiente('eventos_asignacion_personal'),
            'cod_asignacion_personal' => $asignacion->getKey(), 'numero_evento' => ($previo?->numero_evento ?? 0) + 1,
            'cod_evento_anterior' => $previo?->cod_evento_asignacion, 'tipo_evento' => $tipo, 'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $asignacion->estado, 'fecha_hora_efecto' => $efecto, 'fecha_hora_registro' => now(),
            'cod_usuario_registro' => $actor->getKey(), 'motivo' => $motivo,
        ]);
    }
}
