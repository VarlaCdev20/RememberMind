<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\IdentidadesConocimiento;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\FuentesObservacionalesCognitivas;
use App\Modulos\ApoyoDecision\Dominio\Puertos\FuenteConocimientoOrion;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;
use MotorExperto\dominio\conocimiento\versiones\PaqueteCognitivoInstitucional as P;

/** Registro atómico por Medicina/Psicología; no atribuye firmas ni modifica historias de residentes. */
final class RegistrarConocimientoInstitucional
{
    public function ejecutar(User $actor): string
    {
        return app(CodigosEntidad::class)->serializar('versiones_modelo_experto', function () use ($actor): string {
            $actor = User::query()->findOrFail($actor->getKey());
            Gate::forUser($actor)->authorize('gestionarReglas', VersionModeloExperto::class);
            $existente = VersionModeloExperto::query()->where('codigo_version', P::CODIGO)->lockForUpdate()->first();
            if ($existente) {
                if ($existente->observacion !== P::FUNDAMENTO) {
                    throw new DomainException('El código institucional ya identifica otro contenido; no se sobrescribe.');
                }

                return $existente->getKey();
            }
            $fecha = now()->format('Y-m-d H:i:s');
            $id = app(CodigosEntidad::class)->siguiente('versiones_modelo_experto');
            $red = (new PerfilConceptualCognitivo)->construir(app(FuenteConocimientoOrion::class)->leer());
            $vinculos = app(FuentesObservacionalesCognitivas::class)->vinculos();
            $p = (new P)->construir($red, $id, $actor->getKey(), $fecha, $vinculos);
            $cabecera = ['cod_version_modelo' => $id, 'codigo_version' => P::CODIGO, 'nombre' => 'Conocimiento cognitivo institucional',
                'descripcion' => P::FUNDAMENTO, 'estado' => 'ACTIVO', 'fecha_hora_creacion' => $fecha, 'fecha_hora_vigencia' => $fecha,
                'fecha_hora_retiro' => null, 'motivo_cambio' => 'Adopción explícita del contrato A–D por la propietaria.',
                'cod_usuario_creacion' => $actor->getKey(), 'cod_version_anterior' => null, 'observacion' => P::FUNDAMENTO];
            $tablas = app(IdentidadesConocimiento::class)->traducir(['versiones_modelo_experto' => [$cabecera]] + $p->declaracion()['tablas'], $id, $id, false);
            foreach ($tablas as $tabla => $filas) {
                if ($filas !== []) {
                    \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::insertar($tabla, $filas);
                }
            }
            $auditoria = activity('conocimiento_experto')->causedBy($actor)->event('contrato_institucional_registrado')
                ->withProperties(['version' => $id, 'reglas' => 17, 'componentes' => 19, 'fuentes_vinculadas' => count($vinculos),
                    'revision_profesional' => 'PENDIENTE', 'interpretaciones_inventadas' => false])
                ->log('Conocimiento A–D registrado sin alterar fuentes ni atribuir aprobaciones profesionales.');
            if (! $auditoria) {
                throw new DomainException('No se confirmó la auditoría del registro de conocimiento.');
            }

            return $id;
        });
    }
}
