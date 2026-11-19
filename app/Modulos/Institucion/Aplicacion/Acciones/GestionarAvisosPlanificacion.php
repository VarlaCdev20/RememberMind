<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Models\AsignacionPersonal;
use App\Models\EventoAsignacionPersonal;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Consultas\ConsultarAvisosPlanificacion;
use App\Modulos\Institucion\Infraestructura\Jobs\PublicarAvisoPlanificacion;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class GestionarAvisosPlanificacion
{
    public function camposIniciales(AsignacionPersonal $asignacion): array
    {
        if (! config('remembermind.avisos_planificacion_habilitados', false)) return [];
        abort_unless(app(ConsultarAvisosPlanificacion::class)->disponible(), 409, 'Falta instalar el seguimiento aprobado de avisos de planificación.');
        if (DB::transactionLevel() === 0) throw new \LogicException('El aviso debe conservarse junto con el acto original.');
        return ['cod_usuario_afectado_aviso' => $asignacion->personal->cod_usuario,
            'estado_aviso_planificacion' => 'PENDIENTE', 'numero_intentos_aviso' => 0];
    }

    public function despuesDeConfirmar(string $evento): void
    {
        DB::afterCommit(function () use ($evento) {
            try { PublicarAvisoPlanificacion::dispatch($evento); }
            catch (\Throwable $error) {
                // El cambio ya está confirmado. El evento sigue pendiente para recuperación.
                report($error);
            }
        });
    }

    public function publicar(string $codigo, ?User $solicitante = null): string
    {
        abort_unless(app(ConsultarAvisosPlanificacion::class)->disponible(), 409);
        try {
            return DB::transaction(function () use ($codigo, $solicitante) {
                $evento = EventoAsignacionPersonal::lockForUpdate()->findOrFail($codigo);
                if ($solicitante) $this->autorizarReintento($solicitante, $evento);
                if ($evento->estado_aviso_planificacion === 'DISPONIBLE') return 'DISPONIBLE';
                abort_unless(in_array($evento->estado_aviso_planificacion, ['PENDIENTE', 'FALLIDO'], true), 409, 'Este evento no conserva seguimiento de aviso.');
                $instante = now();
                $evento->update(['estado_aviso_planificacion' => 'DISPONIBLE', 'numero_intentos_aviso' => $evento->numero_intentos_aviso + 1,
                    'fecha_hora_ultimo_intento_aviso' => $instante, 'fecha_hora_disponible_aviso' => $instante, 'categoria_fallo_aviso' => null]);
                activity('Planificación')->performedOn($evento)->event('aviso_planificacion_disponible')
                    ->withProperties(['numero_intento' => $evento->numero_intentos_aviso])->log('Aviso compartido disponible en la bandeja interna; no acredita lectura.');
                return 'DISPONIBLE';
            });
        } catch (\Throwable $error) {
            if ($error instanceof HttpExceptionInterface || $error instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) throw $error;
            report($error);
            return DB::transaction(function () use ($codigo, $solicitante) {
                $evento = EventoAsignacionPersonal::lockForUpdate()->findOrFail($codigo);
                if ($solicitante) $this->autorizarReintento($solicitante, $evento);
                if ($evento->estado_aviso_planificacion === 'DISPONIBLE') return 'DISPONIBLE';
                abort_unless(in_array($evento->estado_aviso_planificacion, ['PENDIENTE', 'FALLIDO'], true), 409);
                $evento->update(['estado_aviso_planificacion' => 'FALLIDO', 'numero_intentos_aviso' => $evento->numero_intentos_aviso + 1,
                    'fecha_hora_ultimo_intento_aviso' => now(), 'fecha_hora_disponible_aviso' => null, 'categoria_fallo_aviso' => 'FALLO_DISPONIBILIZACION']);
                activity('Planificación')->performedOn($evento)->event('aviso_planificacion_fallido')
                    ->withProperties(['numero_intento' => $evento->numero_intentos_aviso])->log('No se pudo preparar el aviso compartido; la asignación confirmada permanece guardada.');
                return 'FALLIDO';
            });
        }
    }

    private function autorizarReintento(User $actor, EventoAsignacionPersonal $evento): void
    {
        $actor = $actor->fresh();
        abort_unless($actor && app(ConsultarAvisosPlanificacion::class)->gestiona($actor), 403);
        abort_unless($actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR']) || $evento->cod_usuario_registro === $actor->getKey(), 403);
    }

    public function reintentar(User $actor, string $codigo): string
    {
        $actor = $actor->fresh();
        abort_unless($actor && app(ConsultarAvisosPlanificacion::class)->gestiona($actor), 403);
        return $this->publicar($codigo, $actor);
    }
}
