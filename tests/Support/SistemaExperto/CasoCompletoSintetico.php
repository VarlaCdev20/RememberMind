<?php

namespace Tests\Support\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Acciones\PersistirEvaluacionTecnica;
use App\Backend\Modulos\SistemaExperto\Adaptadores\AdaptadorFuentesCOGMEM;
use App\Backend\Modulos\SistemaExperto\Conocimiento\CargadorConocimiento;
use App\Backend\Modulos\SistemaExperto\Conocimiento\EjemploTecnicoCOGMEM as F;
use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\DTO\ContextoEjecucion;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;
use App\Backend\Modulos\SistemaExperto\Servicios\LecturaTecnicaPruebas;
use App\Models\AplicacionInstrumento;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/** Caso reproducible: fuentes reales de QA -> adaptación -> motor -> persistencia. */
final class CasoCompletoSintetico
{
    public static function crear(): array
    {
        if (! LecturaTecnicaPruebas::habilitada()) {
            throw new RuntimeException('El caso completo requiere una base desechable y lectura técnica explícita.');
        }
        if (DB::table('usuarios')->exists()) {
            throw new RuntimeException('El caso requiere una base de pruebas vacía; no sobrescribe datos existentes.');
        }

        return DB::transaction(function (): array {
            [$autor, $residente] = FixtureLecturaExperta::crear(false);
            DB::table('personal')->where('cod_personal', 'PER_MED')->update([
                'nombres' => 'Lucía', 'apellido_paterno' => 'Prueba', 'apellido_materno' => 'Técnica',
            ]);
            DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST')->update([
                'codigo_version' => 'QA-COMPLETO-1', 'nombre' => 'Caso artificial completo de memoria',
                'observacion' => 'Exclusivo de QA. Sin aprobación ni uso clínico. No emplea reactivos protegidos.',
            ]);
            foreach (['RULE_A' => 'Patrón artificial con dificultad', 'RULE_B' => 'Patrón artificial sin dificultad'] as $id => $nombre) {
                DB::table('reglas_expertas')->where('cod_regla_experta', $id)->update(['nombre' => $nombre]);
            }
            DB::table('fuentes_datos_expertas')->where('cod_fuente_dato_experta', 'FU_INST')->update(['nombre' => 'Prueba instrumental sintética completa']);
            DB::table('nodos_semanticos')->where('cod_nodo_semantico', 'INS')->update(['nombre' => 'Componente instrumental artificial de memoria']);

            $password = bin2hex(random_bytes(18));
            $enfermera = User::create(['cod_usuario' => 'USR_ENF_QA', 'correo' => 'enfermeria-qa@example.test', 'contrasena' => Hash::make($password), 'estado' => 'ACTIVO']);
            $enfermera->assignRole('ENFERMEROS');
            // Permiso explícito de esta cuenta sintética, solo en la BDD aislada.
            $enfermera->givePermissionTo('aplicaciones_instrumento.ver');
            Personal::create(['cod_personal' => 'PER_ENF_QA', 'cod_usuario' => $enfermera->getKey(),
                'nombres' => 'Rosa', 'apellido_paterno' => 'Prueba', 'apellido_materno' => 'Sintética',
                'numero_documento' => 'QA-ENF-001', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
            Turno::create(['cod_turno' => 'TUR_QA', 'nombre' => 'Turno de prueba', 'orden' => 1, 'hora_inicio' => '00:00:00', 'hora_cierre' => '23:59:59', 'estado' => 'ACTIVO']);
            Jornada::create(['cod_jornada' => 'JOR_QA', 'cod_turno' => 'TUR_QA', 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
            AsignacionResidenteJornada::create(['cod_asignacion' => 'ARJ_QA', 'cod_jornada' => 'JOR_QA',
                'cod_personal' => 'PER_ENF_QA', 'cod_residente' => $residente->getKey(), 'fecha_hora' => now(), 'estado' => 'ACTIVA']);

            DB::table('instrumentos')->insert(['cod_instrumento' => 'INST_COMPLETO', 'codigo' => 'QA-MEMORIA',
                'nombre' => 'Prueba artificial de memoria · tres tareas técnicas', 'tipo' => 'PRUEBA_TECNICA',
                'version' => 'TECNICA_1', 'estado' => 'INACTIVO']);
            $preguntas = [
                'QA_REC' => ['Recuerdo inmediato de un elemento artificial', 'NO_RECORDADO'],
                'QA_REM' => ['Recuerdo diferido del mismo elemento artificial', 'NO_RECORDADO'],
                'QA_IND' => ['Seguimiento de una instrucción artificial sencilla', 'COMPLETADO'],
            ];
            $fechaFuente = now()->subMinutes(15)->format('Y-m-d H:i:s');
            DB::table('aplicaciones_instrumento')->insert(['cod_aplicacion' => 'APP_COMPLETA', 'cod_instrumento' => 'INST_COMPLETO',
                'cod_residente' => $residente->getKey(), 'cod_personal' => 'PER_MED', 'fecha_hora' => $fechaFuente,
                'estado' => 'COMPLETA', 'observacion' => 'Respuestas generadas para la prueba técnica; no se aplicó una evaluación clínica.']);
            $requeridas = [];
            $patron = [];
            foreach ($preguntas as $id => [$enunciado, $valor]) {
                DB::table('preguntas_instrumento')->insert(['cod_pregunta' => $id, 'cod_instrumento' => 'INST_COMPLETO',
                    'codigo' => $id, 'enunciado' => $enunciado, 'tipo_respuesta' => 'TEXTO', 'orden' => count($patron) + 1, 'estado' => 'INACTIVO']);
                DB::table('respuestas_instrumento')->insert(['cod_respuesta' => 'RESP_'.$id, 'cod_aplicacion' => 'APP_COMPLETA', 'cod_pregunta' => $id, 'valor_texto' => $valor]);
                $requeridas[$id] = ['campo' => 'valor_texto', 'tipo' => 'string', 'tipo_respuesta' => 'TEXTO', 'valores_permitidos' => [$valor]];
                $patron[$id] = $valor;
            }
            $f = F::paquete();
            $tablas = (new CargadorConocimiento)->instantanea('VER_TEST');
            $paquete = new PaqueteConocimiento('VER_TEST', $tablas, $f->contratosRelaciones, true, 'RESULTADO', $f->contratosExtraccion);
            $memoria = new MemoriaTrabajo(new ContextoEjecucion('EVAL_COMPLETA', $residente->getKey(), 'VER_TEST', new DateTimeImmutable(now()->toIso8601String())));
            $componente = ['solo_pruebas_tecnicas' => true, 'instrumento' => 'INST_COMPLETO', 'version_instrumento' => 'TECNICA_1',
                'componente' => 'COMP_TEST', 'mapeo_variable' => 'MAP_INS', 'metodo' => 'PATRON_ARTIFICIAL_EXACTO',
                'preguntas_requeridas' => $requeridas, 'patrones' => [['respuestas' => $patron, 'valor_fuente' => 'literal_A']]];
            $aplicacion = AplicacionInstrumento::query()->with(['instrumento', 'evaluador', 'respuestas'])->findOrFail('APP_COMPLETA');
            $adaptada = (new AdaptadorFuentesCOGMEM)->extraerAplicacion($aplicacion, $paquete, $memoria, $componente);
            if (! $adaptada['inspeccion']['verificado']) {
                throw new RuntimeException('El componente artificial completo no pudo verificarse.');
            }
            $memoria->vincular('MEM', $adaptada['evidencia']->id, 'INSTRUMENTAL');
            $id = (new PersistirEvaluacionTecnica)->ejecutar($paquete, $memoria, 'MEM', F::contextoClinico(), $autor, ['MAP_INS' => $componente]);

            // Artefacto de la ejecución QA, separado del historial clínico normalizado.
            $informe = ['evaluacion' => $id, 'residente' => $residente->getKey(), 'version' => 'VER_TEST',
                'fecha_corte' => $memoria->contexto->fechaCorte->format(DATE_ATOM), 'fecha_fuente' => $fechaFuente,
                'instrumento' => 'Prueba artificial de memoria · tres tareas técnicas', 'version_instrumento' => 'TECNICA_1',
                'componente' => 'COMP_TEST', 'metodo' => $componente['metodo'], 'valor_fuente' => 'literal_A',
                'mapeo' => $adaptada['evidencia']->mapeoVariable, 'valor_semantico' => $adaptada['evidencia']->valor,
                'respuestas' => collect($preguntas)->map(fn ($p) => ['pregunta' => $p[0], 'valor' => $p[1]])->values()->all(),
                'verificaciones' => ['Tres respuestas requeridas presentes' => true, 'Preguntas del instrumento correcto' => true,
                    'Versión y componente exactos' => true, 'Patrón de respuestas reconocido' => true,
                    'Fuente reextraída antes de guardar' => true],
                'contexto' => F::contextoClinico()];
            config()->set('sistema_experto.lectura_tecnica_pruebas.informe', $informe);

            return ['usuario' => $enfermera, 'residente' => $residente, 'evaluacion' => $id,
                'password' => $password, 'inspeccion' => $adaptada['inspeccion'], 'componente' => $componente,
                'informe' => $informe,
                'fecha_corte' => $memoria->contexto->fechaCorte->format(DATE_ATOM)];
        });
    }
}
