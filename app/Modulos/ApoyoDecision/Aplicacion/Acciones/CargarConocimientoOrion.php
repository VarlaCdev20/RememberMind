<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Acciones;

use App\Models\User;
use App\Modulos\ApoyoDecision\Dominio\Puertos\FuenteConocimientoOrion;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Carga documental autorizada. No activa inferencia ni modifica actos clínicos. */
final class CargarConocimientoOrion
{
    public const VERSION = 'VER_ORION_20261008';

    public function __construct(private readonly FuenteConocimientoOrion $fuente) {}

    public function ejecutar(User $autor): array
    {
        $datos = $this->fuente->leer();

        return app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->serializar('versiones_modelo_experto', function () use ($autor, $datos): array {
            $usuario = User::query()->lockForUpdate()->findOrFail($autor->getKey());
            if ($usuario->estado !== 'ACTIVO' || ! $usuario->hasRole('SUPERADMINISTRADOR')
                || ! $usuario->checkPermissionTo('auditoria.ver', 'web')) {
                throw new DomainException('La carga documental requiere una cuenta de Superadministración activa e identificada.');
            }
            $version = DB::table('versiones_modelo_experto')->where('codigo_version', 'ORION-V1-20261008')->lockForUpdate()->first();
            $versionReal = $version?->cod_version_modelo ?? app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('versiones_modelo_experto');
            $fecha = $version?->fecha_hora_creacion ?? now()->format('Y-m-d H:i:s');
            $creador = $version?->cod_usuario_creacion ?? $usuario->getKey();
            $sello = ['cod_version_modelo' => self::VERSION, 'estado' => 'INACTIVO',
                'fecha_hora_creacion' => $fecha, 'cod_usuario_creacion' => $creador];
            $filas = ['versiones_modelo_experto' => [$sello + [
                'codigo_version' => 'ORION-V1-20261008', 'nombre' => 'O.R.I.O.N. V1 · conocimiento documental',
                'descripcion' => $datos['limite'], 'fecha_hora_vigencia' => null, 'fecha_hora_retiro' => null,
                'motivo_cambio' => 'Carga autorizada expresamente por la propietaria el 08/10/2026.',
                'cod_version_anterior' => null, 'observacion' => 'Documento Maestro SHA-256: '.$datos['documento_maestro_sha256']
                    .'; matrices SHA-256: '.$datos['matrices_sha256'].'. Carga documental; sin activación clínica.',
            ]]];
            foreach ($datos['nodos'] as $nodo) {
                $filas['nodos_semanticos'][] = $sello + ['cod_nodo_semantico' => $this->codigo('NS', $nodo['codigo']),
                    'codigo_semantico' => $nodo['codigo'], 'nombre' => $nodo['nombre'], 'tipo_nodo' => $nodo['tipo'],
                    'definicion' => $nodo['definicion'], 'observacion' => '2_Conceptos · filas '.implode(', ', $nodo['procedencia'])
                        .' · estado documental: '.$nodo['estado_fuente'].'. '.$nodo['limites']];
            }
            foreach ($datos['relaciones'] as $relacion) {
                // D-123 separa el estado CANDIDATA del predicado SUBCOMPONENTE_DE.
                $tipo = $relacion['relacion'] === 'SUBCOMPONENTE_CANDIDATO_DE' ? 'SUBCOMPONENTE_DE' : $relacion['relacion'];
                $filas['relaciones_semanticas'][] = $sello + ['cod_relacion_semantica' => $this->codigo('RS', $relacion['codigo']),
                    'cod_nodo_origen' => $this->codigo('NS', $relacion['origen']), 'tipo_relacion' => $tipo,
                    'modalidad_relacion' => null, 'cod_nodo_destino' => $this->codigo('NS', $relacion['destino']),
                    'significado' => $relacion['significado'], 'observacion' => '4_Relaciones · fila '.$relacion['fila']
                        .' · '.$relacion['codigo'].' · predicado documental: '.$relacion['relacion']
                        .' · estado documental: '.$relacion['estado_fuente'].'. '.$relacion['limites']];
            }
            $filas = app(\App\Modulos\ApoyoDecision\Aplicacion\Contratos\IdentidadesConocimiento::class)->traducir($filas, self::VERSION, $versionReal, $version !== null);
            $claves = ['versiones_modelo_experto' => 'cod_version_modelo', 'nodos_semanticos' => 'cod_nodo_semantico',
                'relaciones_semanticas' => 'cod_relacion_semantica'];
            if ($version) {
                foreach ($filas as $tabla => $esperadas) {
                    $guardadas = DB::table($tabla)->where('cod_version_modelo', $versionReal)->get()->keyBy($claves[$tabla]);
                    if ($guardadas->count() !== count($esperadas)) {
                        throw new DomainException('La versión documental conservada no coincide con la carga. No se sobrescribe.');
                    }
                    foreach ($esperadas as $fila) {
                        if ((array) $guardadas->get($fila[$claves[$tabla]]) != $fila) {
                            throw new DomainException('El conocimiento conservado difiere de la fuente. Requiere otra versión.');
                        }
                    }
                }
            } else {
                foreach ($filas as $tabla => $contenido) {
                    DB::table($tabla)->insert($contenido);
                }
                activity('sistema_experto')->causedBy($usuario)->event('conocimiento_documental_cargado')
                    ->withProperties(['version' => $versionReal, 'nodos' => count($datos['nodos']),
                        'relaciones' => count($datos['relaciones']), 'activacion_clinica' => false])
                    ->log('Carga documental de O.R.I.O.N. autorizada por la propietaria.');
            }

            return ['version' => $versionReal, 'nodos' => count($datos['nodos']), 'relaciones' => count($datos['relaciones']),
                'cargado_previamente' => $version !== null, 'activacion_clinica' => false];
        });
    }

    private function codigo(string $prefijo, string $semantico): string
    {
        return $prefijo.'_'.substr(hash('sha256', self::VERSION.'|'.$semantico), 0, 16);
    }
}
