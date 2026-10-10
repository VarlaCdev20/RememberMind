<?php

namespace Tests\Support\SistemaExperto;

use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Backend\Modulos\SistemaExperto\Conocimiento\EjemploTecnicoCOGMEM as F;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\Personal;
use App\Models\Preadmision;
use App\Models\Residente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/** Datos artificiales de lectura, exclusivamente para pruebas aisladas. */
final class FixtureLecturaExperta
{
    public static function contrato(): array
    {
        return ['criterios' => ['COG-MEM'], 'estados_ejecucion' => ['SINTETICA_FINAL'],
            'componentes_memoria' => ['MAP_INS'],
            'estados_inferencia' => ['SINTETICA_FINAL'], 'roles_soporte' => ['INSTRUMENTAL'],
            'estados_participacion_soporte' => ['SINTETICA_USADA']];
    }

    public static function crear(bool $conHistorialArtificial = true): array
    {
        $c = DB::connection();
        if (! app()->environment('testing') || ! (($c->getDriverName() === 'sqlite' && ($c->getDatabaseName() === ':memory:' || str_ends_with(str_replace('\\', '/', $c->getDatabaseName()), '/remembermind-medical-results-qa/qa-medical.sqlite')))
            || ($c->getDriverName() === 'pgsql' && preg_match('/^remembermind_experto_test_[0-9]{8}_[a-z0-9]+$/D', $c->getDatabaseName())))) {
            throw new RuntimeException('Fixture solo para BDD de prueba aislada.');
        }
        (new RolesAndPermissionsSeeder)->run();
        foreach (['ADM' => 'ADMINISTRADOR', 'MED' => 'MEDICO GENERAL/GERIATRA'] as $id => $rol) {
            $u = User::create(['cod_usuario' => 'USR_'.$id, 'correo' => strtolower($id).'@example.test', 'contrasena' => Hash::make(bin2hex(random_bytes(20))), 'estado' => 'ACTIVO']);
            $u->assignRole($rol);
        }
        $medico = User::findOrFail('USR_MED');
        Personal::create(['cod_personal' => 'PER_MED', 'cod_usuario' => $medico->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético', 'numero_documento' => 'QA-MED', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        Habitacion::create(['cod_habitacion' => 'HAB_QA', 'codigo' => 'QA', 'capacidad' => 1, 'estado' => 'ACTIVA']);
        Cama::create(['cod_cama' => 'CAM_QA', 'cod_habitacion' => 'HAB_QA', 'codigo' => 'QA', 'estado' => 'ACTIVA']);
        $pre = Preadmision::create(['cod_preadmision' => 'PRE_QA', 'cod_usuario_registro' => 'USR_ADM', 'nombres' => 'María Elena', 'apellido_paterno' => 'Sintética', 'apellido_materno' => 'Evaluación', 'fecha_nacimiento' => '1948-02-03', 'motivo_ingreso' => 'Prueba de interfaz', 'fecha_solicitud' => '2026-10-08 09:00:00', 'estado' => 'APROBADA']);
        $residente = (new FormalizarAdmision)->ejecutar($pre, ['cod_cama' => 'CAM_QA', 'contacto' => ['nombres' => 'Contacto', 'apellido_paterno' => 'Sintético']], User::findOrFail('USR_ADM'));
        self::insertar('versiones_modelo_experto', ['cod_version_modelo' => 'VER_TEST', 'codigo_version' => 'ARTIFICIAL_1', 'nombre' => 'Conocimiento artificial de lectura', 'estado' => 'INACTIVO', 'cod_usuario_creacion' => 'USR_MED']);
        foreach (F::tablas() as $tabla => $filas) {
            foreach ($filas as $fila) {
                if (isset($fila['cod_usuario_creacion'])) {
                    $fila['cod_usuario_creacion'] = 'USR_MED';
                }
                if ($tabla === 'dominios_valores_expertos') {
                    $fila['codigo_dominio'] = $fila['cod_dominio_valores'] === 'DOM_RES' ? 'DOM-RESULTADO-COG-MEM' : 'DOM-ARTIFICIAL';
                }
                self::insertar($tabla, $fila);
            }
        }
        if (! $conHistorialArtificial) {
            return [$medico, $residente];
        }
        self::evaluacion($residente->getKey(), 'D', 'EV-CM-2', 'RES_D');
        self::evaluacion($residente->getKey(), 'S', 'EV-CM-2', 'RES_S');
        self::evaluacion($residente->getKey(), 'M', 'EV-CM-2', 'RES_M');
        self::evaluacion($residente->getKey(), 'P', 'EV-CM-1');
        self::evaluacion($residente->getKey(), 'I', 'EV-CM-0');
        config()->set('sistema_experto.lectura_clinica', ['VER_TEST' => self::contrato()]);

        return [$medico, $residente];
    }

    public static function insertar(string $tabla, array $fila): void
    {
        $contrato = json_decode(file_get_contents(base_path('tests/Fixtures/SistemaExperto/contrato-d137.json')), true, 512, JSON_THROW_ON_ERROR);
        $def = collect($contrato['tables'])->firstWhere('name', $tabla);
        foreach ($def['columns'] as $col) {
            if (array_key_exists($col['name'], $fila)) {
                continue;
            }
            $fila[$col['name']] = $col['nullable'] ? null : match ($col['type']) {
                'dateTime' => '2026-10-08 12:00:00',
                default => $col['name'] === 'cod_usuario_creacion' ? 'USR_MED' : 'TEST_'.reset($fila),
            };
        }
        DB::table($tabla)->insert($fila);
    }

    public static function residenteAdicional(string $id): Residente
    {
        Habitacion::create(['cod_habitacion' => 'HAB_'.$id, 'codigo' => $id, 'capacidad' => 1, 'estado' => 'ACTIVA']);
        Cama::create(['cod_cama' => 'CAM_'.$id, 'cod_habitacion' => 'HAB_'.$id, 'codigo' => $id, 'estado' => 'ACTIVA']);
        $pre = Preadmision::create(['cod_preadmision' => 'PRE_'.$id, 'cod_usuario_registro' => 'USR_ADM', 'nombres' => 'Residente', 'apellido_paterno' => 'Sintético adicional', 'fecha_nacimiento' => '1950-06-02', 'motivo_ingreso' => 'Prueba aislada', 'fecha_solicitud' => '2026-10-08 09:00:00', 'estado' => 'APROBADA']);

        return (new FormalizarAdmision)->ejecutar($pre, ['cod_cama' => 'CAM_'.$id, 'contacto' => ['nombres' => 'Contacto', 'apellido_paterno' => 'Sintético']], User::findOrFail('USR_ADM'));
    }

    private static function evaluacion(string $residente, string $id, string $ev, ?string $resultado = null): void
    {
        self::insertar('evaluaciones_expertas', ['cod_evaluacion_experta' => 'EVAL_'.$id, 'cod_residente' => $residente, 'cod_version_modelo' => 'VER_TEST', 'cod_personal_solicitante' => 'PER_MED', 'origen_activacion' => 'SINTETICA', 'estado_ejecucion' => 'SINTETICA_FINAL']);
        self::insertar('evaluacion_criterios', ['cod_evaluacion_criterio' => 'EC_'.$id, 'cod_evaluacion_experta' => 'EVAL_'.$id, 'cod_nodo_criterio' => 'MEM', 'estado_evaluabilidad' => $ev]);
        if (! $resultado) {
            return;
        }
        self::insertar('resultados_criterio', ['cod_resultado_criterio' => 'RC_'.$id, 'cod_evaluacion_criterio' => 'EC_'.$id, 'cod_valor_semantico' => $resultado]);
        self::insertar('trazas_inferencia', ['cod_traza_inferencia' => 'TR_'.$id, 'cod_evaluacion_criterio' => 'EC_'.$id, 'estado_inferencia' => 'SINTETICA_FINAL']);
        foreach ($id === 'M' ? ['A', 'B'] : [$id === 'D' ? 'A' : 'B'] as $v) {
            $eid = 'EV_'.$id.'_'.$v;
            self::insertar('evidencias_evaluacion', ['cod_evidencia_evaluacion' => $eid, 'cod_evaluacion_experta' => 'EVAL_'.$id, 'cod_mapeo_variable_fuente' => 'MAP_INS', 'cod_registro_fuente' => 'APP_'.$id.'_'.$v, 'estado_representacion' => 'MAPEADO', 'cod_valor_semantico' => 'VAL_'.$v, 'estado_admisibilidad' => 'ADMISIBLE']);
            self::insertar('evidencias_criterio_evaluacion', ['cod_evidencia_criterio' => 'EP_'.$id.'_'.$v, 'cod_evaluacion_criterio' => 'EC_'.$id, 'cod_evidencia_evaluacion' => $eid, 'rol_en_criterio' => 'INSTRUMENTAL', 'estado_participacion' => 'SINTETICA_USADA']);
            self::insertar('evaluaciones_reglas', ['cod_evaluacion_regla' => 'ER_'.$id.'_'.$v, 'cod_traza_inferencia' => 'TR_'.$id, 'cod_regla_experta' => 'RULE_'.$v, 'estado_regla' => 'CUMPLE']);
            self::insertar('evaluaciones_condiciones_regla', ['cod_evaluacion_condicion' => 'CV_'.$id.'_'.$v, 'cod_evaluacion_regla' => 'ER_'.$id.'_'.$v, 'cod_condicion_regla' => 'COND_'.$v, 'estado_condicion' => 'CUMPLE']);
            self::insertar('evidencias_soporte_condicion', ['cod_evidencia_soporte_condicion' => 'SUP_'.$id.'_'.$v, 'cod_evaluacion_condicion' => 'CV_'.$id.'_'.$v, 'cod_evidencia_evaluacion' => $eid]);
        }
    }
}
