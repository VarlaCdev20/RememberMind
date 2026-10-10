<?php

namespace App\Backend\Modulos\SistemaExperto\Acciones;

use App\Backend\Modulos\SistemaExperto\Conocimiento\CargadorConocimiento;
use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteInvestigadoCOGMEM as P;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Registro de una propuesta: nunca cambia vigencias, aprobaciones o historia clínica. */
final class CargarPaqueteInvestigadoCOGMEM
{
    public function ejecutar(User $autor): array
    {
        return DB::transaction(function () use ($autor): array {
            $usuario = User::query()->lockForUpdate()->findOrFail($autor->getKey());
            if ($usuario->estado !== 'ACTIVO' || ! $usuario->hasRole('SUPERADMINISTRADOR')
                || ! $usuario->checkPermissionTo('auditoria.ver', 'web')) {
                throw new DomainException('La carga requiere Superadministración activa y permiso de auditoría.');
            }
            $guardada = DB::table('versiones_modelo_experto')->where('cod_version_modelo', P::VERSION)->lockForUpdate()->first();
            $fecha = $guardada?->fecha_hora_creacion ?? now()->format('Y-m-d H:i:s');
            $creador = $guardada?->cod_usuario_creacion ?? $usuario->getKey();
            $p = new P;
            $p->paquete($creador, $fecha);
            $tablas = $p->tablas($creador, $fecha);
            $version = ['cod_version_modelo' => P::VERSION, 'codigo_version' => P::CODIGO,
                'nombre' => 'COG-MEM · propuesta observacional investigada', 'descripcion' => P::LIMITES,
                'estado' => 'INACTIVO', 'fecha_hora_creacion' => $fecha, 'fecha_hora_vigencia' => null,
                'fecha_hora_retiro' => null, 'motivo_cambio' => 'Investigación e implementación solicitadas por la propietaria el 08/10/2026.',
                'cod_usuario_creacion' => $creador, 'cod_version_anterior' => null,
                'observacion' => 'Fuentes consultadas el 08/10/2026: '.implode(' ; ', P::FUENTES)
                    .'. Sin dictamen profesional de este contenido nuevo; no reemplaza ORION-V1.'];
            if ($guardada) {
                if ((array) $guardada != $version) {
                    throw new DomainException('La propuesta conservada difiere. Requiere otra versión; no se sobrescribe.');
                }
                $actual = (new CargadorConocimiento)->instantanea(P::VERSION);
                foreach ($tablas as $tabla => $filas) {
                    $pk = PaqueteConocimiento::CLAVES[$tabla];
                    if (collect($actual[$tabla])->keyBy($pk)->all() != collect($filas)->keyBy($pk)->all()) {
                        throw new DomainException('El contenido conservado difiere. No se sobrescribe ni reactiva.');
                    }
                }
            } else {
                DB::table('versiones_modelo_experto')->insert($version);
                foreach ($tablas as $tabla => $filas) {
                    if ($filas) {
                        DB::table($tabla)->insert($filas);
                    }
                }
                activity('sistema_experto')->causedBy($usuario)->event('paquete_investigado_registrado')
                    ->withProperties(['version' => P::VERSION, 'mapeos_propuestos' => 6, 'activacion_clinica' => false])
                    ->log('Propuesta investigada COG-MEM registrada, sin validación ni inferencia clínica.');
            }

            return ['version' => P::VERSION, 'mapeos_propuestos' => 6, 'activacion_clinica' => false,
                'cargado_previamente' => $guardada !== null];
        });
    }
}
