<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\IdentidadesConocimiento;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\versiones\PaquetePetersenInstitucional as P;

/** Registra conocimiento E–H adoptado, sin crear juicios clínicos ni atribuir una revisión humana. */
final class RegistrarConocimientoPetersen
{
    public function ejecutar(User $actor): string
    {
        return app(CodigosEntidad::class)->serializar('versiones_modelo_experto', function () use ($actor): string {
            $actor = User::query()->findOrFail($actor->getKey());
            Gate::forUser($actor)->authorize('gestionarReglas', VersionModeloExperto::class);
            $existente = VersionModeloExperto::query()->where('codigo_version', P::CODIGO)->lockForUpdate()->first();
            if ($existente) {
                if ($existente->observacion !== P::FUNDAMENTO) {
                    throw new DomainException('El código Petersen ya identifica otro contenido; no se sobrescribe.');
                }

                return $existente->getKey();
            }
            $fecha = now()->format('Y-m-d H:i:s');
            $id = app(CodigosEntidad::class)->siguiente('versiones_modelo_experto');
            $p = (new P)->construir($id, $actor->getKey(), $fecha);
            $cabecera = ['cod_version_modelo' => $id, 'codigo_version' => P::CODIGO, 'nombre' => 'Patrones institucionales de Petersen',
                'descripcion' => P::FUNDAMENTO, 'estado' => 'ACTIVO', 'fecha_hora_creacion' => $fecha, 'fecha_hora_vigencia' => $fecha,
                'fecha_hora_retiro' => null, 'motivo_cambio' => 'Gobierno del contrato E–H ya adoptado por la propietaria.',
                'cod_usuario_creacion' => $actor->getKey(), 'cod_version_anterior' => null, 'observacion' => P::FUNDAMENTO];
            $tablas = app(IdentidadesConocimiento::class)->traducir(['versiones_modelo_experto' => [$cabecera]] + $p->declaracion()['tablas'], $id, $id, false);
            foreach ($tablas as $tabla => $filas) {
                if ($filas !== []) {
                    DB::table($tabla)->insert($filas);
                }
            }
            $auditoria = activity('conocimiento_experto')->causedBy($actor)->event('contrato_petersen_registrado')
                ->withProperties(['version' => $id, 'reglas' => 4, 'contrato' => 'PETERSEN-INSTITUCIONAL-1', 'revision_profesional' => 'PENDIENTE'])
                ->log('Gobierno Petersen registrado sin alterar fuentes ni atribuir aprobaciones profesionales.');
            if (! $auditoria) {
                throw new DomainException('No se confirmó la auditoría del gobierno Petersen.');
            }

            return $id;
        });
    }
}
