<?php

namespace Tests\Support;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM;

/** Construye filas artificiales con sus trazas en testing; nunca ejecuta un motor ni una base operativa. */
final class InformeCognitivoFixture
{
    /** Inserta conocimiento sintético y contratos exclusivos de la ejecución de PHPUnit. */
    public static function conocimiento(User $usuario): void
    {
        self::insertar('versiones_modelo_experto', ['cod_version_modelo' => 'VER_TEST', 'codigo_version' => 'INFORME-LECTURA-01',
            'estado' => 'INACTIVO', 'cod_usuario_creacion' => $usuario->getKey()]);
        foreach (EjemploTecnicoCOGMEM::tablas() as $tabla => $filas) {
            foreach ($filas as $fila) {
                if (isset($fila['cod_usuario_creacion'])) {
                    $fila['cod_usuario_creacion'] = $usuario->getKey();
                }
                if ($tabla === 'dominios_valores_expertos') {
                    $fila['codigo_dominio'] = $fila['cod_dominio_valores'] === 'DOM_RES' ? 'DOM-RESULTADO-COG-MEM' : 'DOM-ARTIFICIAL';
                }
                if ($tabla === 'fuentes_datos_expertas') {
                    $fila['nombre'] = $fila['tabla_raiz'] === 'aplicaciones_instrumento' ? 'Fuente instrumental de memoria' : 'Control cognitivo';
                }
                self::insertar($tabla, $fila);
            }
        }
        config(['sistema_experto.lectura_clinica' => ['VER_TEST' => ['criterios' => ['COG-MEM'], 'estados_ejecucion' => ['FINALIZADA'],
            'componentes_memoria' => ['MAP_INS'], 'estados_inferencia' => ['FINALIZADA'],
            'roles_soporte' => ['INSTRUMENTAL'], 'estados_participacion_soporte' => ['UTILIZADA']]]]);
    }

    /** Conserva un estado concreto y sus soportes; dos rutas mixtas usan registros diferentes. */
    public static function evaluacion(Residente $residente, Personal $personal, string $id, string $ev = 'EV-CM-2', ?string $valor = 'RES_D'): void
    {
        self::insertar('evaluaciones_expertas', ['cod_evaluacion_experta' => 'E_'.$id, 'cod_residente' => $residente->getKey(),
            'cod_version_modelo' => 'VER_TEST', 'cod_personal_solicitante' => $personal->getKey(), 'origen_activacion' => 'MANUAL', 'estado_ejecucion' => 'FINALIZADA']);
        self::insertar('evaluacion_criterios', ['cod_evaluacion_criterio' => 'EC_'.$id, 'cod_evaluacion_experta' => 'E_'.$id,
            'cod_nodo_criterio' => 'MEM', 'estado_evaluabilidad' => $ev, 'tipo_registro' => 'D137']);
        if ($valor === null) {
            return;
        }
        self::insertar('resultados_criterio', ['cod_resultado_criterio' => 'RC_'.$id, 'cod_evaluacion_criterio' => 'EC_'.$id, 'cod_valor_semantico' => $valor]);
        self::insertar('trazas_inferencia', ['cod_traza_inferencia' => 'TR_'.$id, 'cod_evaluacion_criterio' => 'EC_'.$id, 'estado_inferencia' => 'FINALIZADA']);
        foreach ($valor === 'RES_M' ? ['A', 'B'] : [$valor === 'RES_D' ? 'A' : 'B'] as $ruta) {
            $sufijo = $id.'_'.$ruta;
            self::insertar('evidencias_evaluacion', ['cod_evidencia_evaluacion' => 'EV_'.$sufijo, 'cod_evaluacion_experta' => 'E_'.$id,
                'cod_mapeo_variable_fuente' => 'MAP_INS', 'cod_registro_fuente' => 'APP_'.$sufijo, 'estado_representacion' => 'MAPEADO',
                'cod_valor_semantico' => 'VAL_'.$ruta, 'estado_admisibilidad' => 'ADMISIBLE']);
            self::insertar('evidencias_criterio_evaluacion', ['cod_evidencia_criterio' => 'EP_'.$sufijo, 'cod_evaluacion_criterio' => 'EC_'.$id,
                'cod_evidencia_evaluacion' => 'EV_'.$sufijo, 'rol_en_criterio' => 'INSTRUMENTAL', 'estado_participacion' => 'UTILIZADA',
                'fecha_hora_vinculacion' => now()->subDay()->format('Y-m-d H:i:s')]);
            self::insertar('evaluaciones_reglas', ['cod_evaluacion_regla' => 'ER_'.$sufijo, 'cod_traza_inferencia' => 'TR_'.$id,
                'cod_regla_experta' => 'RULE_'.$ruta, 'estado_regla' => 'CUMPLE']);
            self::insertar('evaluaciones_condiciones_regla', ['cod_evaluacion_condicion' => 'CV_'.$sufijo, 'cod_evaluacion_regla' => 'ER_'.$sufijo,
                'cod_condicion_regla' => 'COND_'.$ruta, 'estado_condicion' => 'CUMPLE']);
            self::insertar('evidencias_soporte_condicion', ['cod_evidencia_soporte_condicion' => 'SUP_'.$sufijo,
                'cod_evaluacion_condicion' => 'CV_'.$sufijo, 'cod_evidencia_evaluacion' => 'EV_'.$sufijo]);
        }
    }

    /** Completa metadatos obligatorios de la fixture respetando defaults y PK/FK del esquema instalado. */
    public static function insertar(string $tabla, array $fila): void
    {
        if (! app()->environment('testing')) {
            throw new \LogicException('La fixture no se utiliza fuera de testing.');
        }
        foreach (Schema::getColumns($tabla) as $columna) {
            $nombre = $columna['name'];
            if (array_key_exists($nombre, $fila) || $columna['default'] !== null) {
                continue;
            }
            $fila[$nombre] = $columna['nullable'] ? null : ($nombre === 'cod_usuario_creacion' ? auth()->id()
                : (str_contains($columna['type_name'], 'time') || str_contains($columna['type_name'], 'date')
                    ? now()->subDay()->format('Y-m-d H:i:s') : 'F4_'.reset($fila)));
        }
        DB::table($tabla)->insert($fila);
    }
}
