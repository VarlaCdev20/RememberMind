<?php

namespace App\Modulos\Cuidados\Infraestructura\Persistencia;

use App\Compartido\Dominio\Calendario\SeleccionSemanal;
use App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas;
use App\Compartido\Infraestructura\Persistencia\ContextosResidente;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class RegistrosConsumo
{
    public const TABLA = 'registros_consumo';

    public const CODIGO = 'cod_registro_consumo';

    public const ORIGENES = [
        'pases_turno' => ['registros_textuales_clinicos', 'cod_registro_textual', 'PASE', 'cod_pase'],
        'seguimientos_pedagogicos' => ['valoraciones_psicopedagogicas', 'cod_valoracion_profesional', 'PEDAGOGICA', 'cod_seguimiento_pedagogico'],
        'documentos' => ['documentos', 'cod_documento', 'ADMINISTRATIVO', 'cod_documento'],
        'documentos_clinicos' => ['documentos', 'cod_documento', 'CLINICO', 'cod_documento_clinico'],
        'valoraciones_dolor' => ['mediciones_clinicas', 'cod_registro_medicion', 'DOLOR', 'cod_valoracion_dolor'],
        'notas_clinicas' => ['registros_textuales_clinicos', 'cod_registro_textual', 'NOTA', 'cod_nota'],
        'indicaciones_clinicas' => ['registros_textuales_clinicos', 'cod_registro_textual', 'INDICACION', 'cod_indicacion'],
        'antecedentes_clinicos' => ['registros_clinicos', 'cod_registro_clinico', 'ANTECEDENTE', 'cod_antecedente'],
        'diagnosticos' => ['registros_clinicos', 'cod_registro_clinico', 'DIAGNOSTICO', 'cod_diagnostico'],
        'alergias' => ['registros_clinicos', 'cod_registro_clinico', 'ALERGIA', 'cod_alergia'],
        'dispositivos_clinicos' => ['registros_clinicos', 'cod_registro_clinico', 'DISPOSITIVO', 'cod_dispositivo'],
        'asignaciones_personal' => ['asignaciones_jornada', 'cod_asignacion_jornada', 'PERSONAL', 'cod_asignacion_personal'],
        'asignaciones_residente_jornada' => ['asignaciones_jornada', 'cod_asignacion_jornada', 'RESIDENTE', 'cod_asignacion'],
        'registros_ingesta' => [self::TABLA, self::CODIGO, 'INGESTA', 'cod_ingesta'],
        'registros_hidratacion' => [self::TABLA, self::CODIGO, 'HIDRATACION', 'cod_hidratacion'],
        'registros_eliminacion' => ['registros_consumo', 'cod_registro_consumo', 'ELIMINACION', 'cod_eliminacion'],
        'registros_movilidad' => ['registros_observaciones_cuidado', 'cod_observacion_cuidado', 'MOVILIDAD', 'cod_movilidad'],
        'registros_conductuales' => ['registros_observaciones_cuidado', 'cod_observacion_cuidado', 'CONDUCTA', 'cod_registro_conductual'],
        'registros_sueno' => ['registros_observaciones_cuidado', 'cod_observacion_cuidado', 'SUENO', 'cod_registro_sueno'],
        'signos_vitales' => ['mediciones_clinicas', 'cod_registro_medicion', 'SIGNOS', 'cod_signo'],
        'mediciones_antropometricas' => ['mediciones_clinicas', 'cod_registro_medicion', 'ANTROPOMETRIA', 'cod_medicion'],
        'valoraciones_psicologicas' => ['valoraciones_psicopedagogicas', 'cod_valoracion_profesional', 'PSICOLOGICA', 'cod_valoracion_psicologica'],
        'valoraciones_nutricionales' => ['valoraciones_profesionales', 'cod_valoracion_profesional', 'NUTRICIONAL', 'cod_valoracion_nutricional'],
        'valoraciones_funcionales' => ['valoraciones_profesionales', 'cod_valoracion_profesional', 'FUNCIONAL', 'cod_valoracion_funcional'],
        'resultados_estudio' => ['registros_estudio', 'cod_registro_estudio', 'RESULTADO', 'cod_resultado_estudio'],
        'informes_estudio' => ['registros_estudio', 'cod_registro_estudio', 'INFORME', 'cod_informe_estudio'],
    ];

    public static function codigo(string $entidad): ?string
    {
        return self::ORIGENES[$entidad][1] ?? null;
    }

    public static function consulta(string $entidad, ?string $alias = null): Builder
    {
        $tabla = self::ORIGENES[$entidad][0] ?? $entidad;
        $consulta = isset(ColumnasRelacionadas::CONTRATOS[$tabla])
            ? ColumnasRelacionadas::consulta(DB::connection(), $tabla, $alias)
            : DB::table($tabla, $alias);

        if (isset(self::ORIGENES[$entidad])) {
            $consulta->where(($alias ? $alias.'.' : '').'tipo_evento', self::ORIGENES[$entidad][2]);
        }

        return $consulta;
    }

    public static function insertar(string $entidad, array $filas): void
    {
        $filas = array_map(fn ($fila) => SeleccionSemanal::preparar($entidad, $fila), $filas);
        if (isset(self::ORIGENES[$entidad])) {
            [$tabla, $codigo, $tipo, $campo] = self::ORIGENES[$entidad];
            foreach ($filas as &$fila) {
                $fila[$codigo] = $fila[$campo];
                $fila['tipo_evento'] = $tipo;
                if ($campo !== $codigo) {
                    unset($fila[$campo]);
                }
            }
            unset($fila);
            if (isset(ContextosResidente::CONTRATOS[$tabla])) {
                ContextosResidente::insertar(DB::connection(), $tabla, $filas);
            } elseif ($tabla === 'valoraciones_profesionales') {
                DB::transaction(function () use ($tabla, $filas): void {
                    DB::table('atenciones')->whereIn('cod_atencion', array_column($filas, 'cod_atencion'))
                        ->orderBy('cod_atencion')->sharedLock()->get(['cod_atencion']);
                    DB::table($tabla)->insert(array_map(fn ($fila) => ColumnasRelacionadas::preparar(DB::connection(), $tabla, $fila), $filas));
                });
            } else {
                DB::table($tabla)->insert($filas);
            }

            return;
        }

        if (isset(ContextosResidente::CONTRATOS[$entidad])) {
            ContextosResidente::insertar(DB::connection(), $entidad, $filas);
            return;
        }

        DB::transaction(function () use ($entidad, $filas): void {
            ColumnasRelacionadas::bloquearEntrada(DB::connection(), $entidad, $filas);
            DB::table($entidad)->insert(array_map(fn ($fila) => ColumnasRelacionadas::preparar(DB::connection(), $entidad, $fila), $filas));
        });
    }
}
