<?php

namespace Database\Seeders;

use App\Models\AdultoMayor;
use App\Models\AsignacionPlazaEnfermeria;
use App\Models\AsignacionVoluntario;
use App\Models\AsistenciaVoluntario;
use App\Models\DispositivoResidente;
use App\Models\DocumentoUsuario;
use App\Models\EvaluacionGeriatrica;
use App\Models\Familiar;
use App\Models\FamiliarAdulto;
use App\Models\HistorialEstadoOperativo;
use App\Models\IncidenteResidente;
use App\Models\InstrumentoGeriatrico;
use App\Models\LesionResidente;
use App\Models\NotaEvolucionMedica;
use App\Models\PaseTurno;
use App\Models\RecepcionTurno;
use App\Models\RegistroCuidado;
use App\Models\SeguimientoLesion;
use App\Models\TipoDocumentoUsuario;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Models\ValoracionEnfermeriaAdmision;
use App\Models\Voluntario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosOperativosCoherentesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $adultos = AdultoMayor::query()->orderBy('cod_am')->take(10)->get();
            $usuarios = User::query()->orderBy('cod_usu')->get();
            $enfermeros = User::role('ENFERMEROS')->orderBy('cod_usu')->get();
            $turnos = TurnoEnfermeria::query()->orderBy('cod_turno')->get();

            if ($adultos->count() < 10 || $usuarios->isEmpty() || $enfermeros->isEmpty() || $turnos->count() < 2) {
                throw new \RuntimeException('Se requieren al menos 10 residentes, un usuario, un enfermero y dos turnos antes de completar los datos operativos.');
            }

            $this->completarVoluntariado($adultos);
            $this->completarFamiliares($adultos);
            $this->completarDocumentosPersonal($usuarios);
            $this->completarPlazas($enfermeros);
            $this->completarValoraciones($adultos, $usuarios->first());
            $this->completarEnfermeria($adultos, $enfermeros, $turnos);
        });

        $this->command?->info('[DatosOperativosCoherentesSeeder] Tablas operativas completadas con un mínimo de 10 registros relacionados.');
    }

    private function completarVoluntariado($adultos): void
    {
        $nombres = [
            ['MELISSA', 'ARCE', 'VARGAS', '7132401'],
            ['RODRIGO', 'PAREDES', 'LÓPEZ', '7132402'],
            ['PAOLA', 'MERCADO', 'RÍOS', '7132403'],
            ['SERGIO', 'CABRERA', 'NÚÑEZ', '7132404'],
            ['VALERIA', 'MOLINA', 'SUÁREZ', '7132405'],
        ];

        foreach ($nombres as $indice => [$nombre, $paterno, $materno, $ci]) {
            Voluntario::firstOrCreate(
                ['ci' => $ci],
                [
                    'nombres' => $nombre,
                    'ap_paterno' => $paterno,
                    'ap_materno' => $materno,
                    'celular' => '72045'.str_pad((string) $indice, 3, '0', STR_PAD_LEFT),
                    'correo' => strtolower("{$nombre}.{$paterno}").'@correo.bo',
                    'fecha_nac' => now()->subYears(28 + $indice)->subMonths($indice)->toDateString(),
                    'fecha_ing' => now()->subMonths(6 - $indice)->toDateString(),
                    'profesion_ocupacion' => ['Estudiante de enfermería', 'Docente jubilado', 'Trabajadora social', 'Músico', 'Estudiante de fisioterapia'][$indice],
                    'area_apoyo_preferente' => ['Acompañamiento', 'Lectura', 'Apoyo social', 'Recreación', 'Movilidad'][$indice],
                    'disponibilidad_inicial' => 'Dos jornadas por semana, previa coordinación.',
                    'estado' => 'ACTIVO',
                ]
            );
        }

        $voluntarios = Voluntario::query()->orderBy('cod_vol')->get();
        $indice = 0;
        while (AsignacionVoluntario::query()->count() < 10) {
            $voluntario = $voluntarios[$indice % $voluntarios->count()];
            $adulto = $adultos[$indice % $adultos->count()];
            AsignacionVoluntario::firstOrCreate(
                ['cod_vol' => $voluntario->cod_vol, 'cod_am' => $adulto->cod_am],
                ['fecha_asig' => now()->subDays(30 - $indice)->toDateString(), 'estado' => 'ACTIVA', 'obser' => 'Acompañamiento coordinado con el equipo de cuidados.']
            );
            $indice++;
        }

        foreach ($voluntarios->take(10) as $indice => $voluntario) {
            AsistenciaVoluntario::firstOrCreate(
                ['cod_asis_vol' => 'AVS_'.str_pad((string) ($indice + 1), 5, '0', STR_PAD_LEFT)],
                [
                    'cod_vol' => $voluntario->cod_vol,
                    'cod_am' => $adultos[$indice]->cod_am,
                    'fecha' => now()->subDays($indice)->toDateString(),
                    'hora_entrada' => '09:00:00',
                    'hora_salida' => '11:00:00',
                    'actividad_realizada' => 'Acompañamiento, conversación y actividad recreativa supervisada.',
                    'observaciones' => 'Jornada realizada sin novedades.',
                    'estado' => 'PRESENTE',
                ]
            );
        }
    }

    private function completarFamiliares($adultos): void
    {
        $datos = [
            ['MARTA', 'SALAZAR', 'ROJAS', '8245101'],
            ['LUIS', 'VARGAS', 'MAMANI', '8245102'],
            ['ELENA', 'TORRICO', 'FLORES', '8245103'],
            ['MARIO', 'CONDORI', 'ARCE', '8245104'],
        ];

        foreach ($datos as $indice => [$nombre, $paterno, $materno, $ci]) {
            Familiar::firstOrCreate(
                ['ci' => $ci],
                [
                    'nombres' => $nombre,
                    'ap_paterno' => $paterno,
                    'ap_materno' => $materno,
                    'parentesco_vinculo' => $indice % 2 === 0 ? 'HIJA' : 'HIJO',
                    'celular' => '76031'.str_pad((string) $indice, 3, '0', STR_PAD_LEFT),
                    'correo' => strtolower("{$nombre}.{$paterno}").'@correo.bo',
                    'direccion' => 'Zona Central, Cochabamba',
                    'zona' => 'Central',
                    'es_responsable' => true,
                    'estado' => 'ACTIVO',
                ]
            );
        }

        $familiares = Familiar::query()->orderBy('cod_fam')->get();
        $indice = 0;
        while (FamiliarAdulto::query()->count() < 10) {
            $familiar = $familiares[$indice % $familiares->count()];
            $adulto = $adultos[$indice % $adultos->count()];
            FamiliarAdulto::firstOrCreate(
                ['cod_fam' => $familiar->cod_fam, 'cod_am' => $adulto->cod_am],
                ['parentesco_vinculo' => $familiar->parentesco_vinculo ?: 'FAMILIAR', 'es_responsable' => true, 'estado' => 'ACTIVO', 'observaciones' => 'Contacto principal autorizado.']
            );
            $indice++;
        }
    }

    private function completarDocumentosPersonal($usuarios): void
    {
        $tipo = TipoDocumentoUsuario::query()->orderBy('cod_tipo_doc')->first();
        if (! $tipo) {
            return;
        }

        $indice = 0;
        while (DocumentoUsuario::query()->count() < 10) {
            $usuario = $usuarios[$indice % $usuarios->count()];
            DocumentoUsuario::firstOrCreate(
                ['cod_usu' => $usuario->cod_usu, 'cod_tipo_doc' => $tipo->cod_tipo_doc],
                [
                    'archivo_path' => 'documentos/personal/registro-'.$usuario->cod_usu.'.pdf',
                    'nombre_original' => 'Registro institucional.pdf',
                    'nombre_documento' => 'Registro institucional',
                    'fecha_subida' => now()->subDays($indice),
                    'estado' => 'VALIDADO',
                    'validado_por' => 'USU_0001',
                    'fecha_validacion' => now()->subDays($indice),
                ]
            );
            $indice++;
            if ($indice > 50) {
                break;
            }
        }
    }

    private function completarPlazas($enfermeros): void
    {
        for ($indice = 0; $indice < 10; $indice++) {
            AsignacionPlazaEnfermeria::firstOrCreate(
                ['plaza' => 'PLAZA-'.str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT), 'fecha' => now()->subDays($indice)->toDateString()],
                ['cod_usu' => $enfermeros[$indice % $enfermeros->count()]->cod_usu, 'tipo' => $indice < 8 ? 'TITULAR' : 'SUPLENCIA', 'motivo' => 'Cobertura programada del servicio de enfermería.']
            );
        }
    }

    private function completarValoraciones($adultos, User $registrador): void
    {
        $instrumentos = InstrumentoGeriatrico::query()->orderBy('cod_instrumento')->get();
        foreach ($adultos as $indice => $adulto) {
            $instrumento = $instrumentos[$indice % $instrumentos->count()];
            EvaluacionGeriatrica::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'cod_instrumento' => $instrumento->cod_instrumento, 'fecha_eval' => now()->subDays($indice)->toDateString()],
                [
                    'evaluador_id' => $registrador->cod_usu,
                    'registrado_por' => $registrador->cod_usu,
                    'puntaje' => 18 + $indice,
                    'puntaje_total' => 18 + $indice,
                    'resultado_cualitativo' => $indice < 3 ? 'Requiere seguimiento' : 'Dentro de parámetros esperados',
                    'categoria_resultado' => $indice < 3 ? 'RIESGO_MODERADO' : 'SIN_RIESGO',
                    'nivel_alerta' => $indice < 3 ? 'PREVENTIVO' : 'NORMAL',
                    'nivel_riesgo' => $indice < 3 ? 'MODERADO' : 'BAJO',
                    'observaciones' => 'Valoración registrada con respuestas completas y revisión profesional.',
                    'estado' => 'COMPLETADA',
                    'estado_eval' => 'COMPLETADA',
                    'datos_formulario' => ['aplicacion' => 'presencial', 'colaboracion' => 'adecuada'],
                ]
            );

            ValoracionEnfermeriaAdmision::firstOrCreate(
                ['cod_am' => $adulto->cod_am],
                [
                    'fecha_valoracion' => now()->subDays(20 - $indice)->toDateString(),
                    'hora_valoracion' => '09:30:00',
                    'estado_general' => 'ESTABLE',
                    'nivel_conciencia' => 'ALERTA',
                    'orientacion' => 'PARCIAL',
                    'comunicacion' => 'CLARA',
                    'hay_dolor' => $indice % 4 === 0,
                    'intensidad_dolor' => $indice % 4 === 0 ? 3 : 0,
                    'ubicacion_dolor' => $indice % 4 === 0 ? 'Rodilla derecha' : null,
                    'movilidad' => $indice % 3 === 0 ? 'CON_APOYO' : 'INDEPENDIENTE',
                    'apoyo_movilidad' => $indice % 3 === 0 ? 'Bastón' : null,
                    'riesgo_caida' => $indice % 3 === 0 ? 'MODERADO' : 'BAJO',
                    'piel_estado' => 'ÍNTEGRA',
                    'hay_heridas' => false,
                    'higiene_ingreso' => 'ADECUADA',
                    'continencia_basica' => 'CONSERVADA',
                    'alimentacion_aparente' => 'BUENA TOLERANCIA',
                    'signos_vitales_iniciales' => 'PA 120/75 mmHg; FC 72 lpm; T 36.5 °C; SpO₂ 95%.',
                    'observacion' => 'Ingreso valorado por enfermería sin signos de alarma inmediata.',
                    'recomendacion_enfermeria' => 'Mantener controles programados y medidas de prevención de caídas.',
                    'estado' => 'COMPLETADA',
                    'registrado_por' => $registrador->cod_usu,
                ]
            );
        }
    }

    private function completarEnfermeria($adultos, $enfermeros, $turnos): void
    {
        foreach ($adultos as $indice => $adulto) {
            $enfermero = $enfermeros[$indice % $enfermeros->count()];
            $turno = $turnos[$indice % $turnos->count()];
            $turnoEntrante = $turnos[($indice + 1) % $turnos->count()];

            HistorialEstadoOperativo::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'fecha_hora' => now()->subDays(10 - $indice)->startOfDay()->addHours(7)],
                ['estado_anterior' => null, 'estado_nuevo' => 'EN_CENTRO', 'motivo' => 'Inicio de seguimiento operativo institucional.', 'registrado_por' => $enfermero->cod_usu]
            );

            RecepcionTurno::firstOrCreate(
                ['cod_turno' => $turno->cod_turno, 'cod_usuario' => $enfermero->cod_usu, 'fecha_hora_recepcion' => now()->subDays($indice)->startOfDay()->addHours(7)->addMinutes($indice)],
                ['observacion' => 'Turno recibido con revisión de pendientes y residentes asignados.']
            );

            DispositivoResidente::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'tipo' => ['BASTÓN', 'ANDADOR', 'SILLA_DE_RUEDAS', 'AUDÍFONO', 'PRÓTESIS_DENTAL'][$indice % 5]],
                ['ubicacion' => $indice % 2 === 0 ? 'Uso personal' : 'Habitación', 'fecha_colocacion' => now()->subDays(30 - $indice), 'estado' => 'ACTIVO', 'indicacion' => 'Uso según necesidad y supervisión del equipo de cuidados.', 'observacion' => 'Dispositivo identificado y en condiciones funcionales.', 'registrado_por' => $enfermero->cod_usu]
            );

            RegistroCuidado::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'tipo' => ['ALIMENTACION', 'HIDRATACION', 'HIGIENE', 'MOVILIDAD', 'SUEÑO'][$indice % 5], 'fecha_hora_evento' => now()->subDays($indice)->startOfDay()->addHours(9)],
                ['cod_turno' => $turno->cod_turno, 'registrado_por' => $enfermero->cod_usu, 'estado' => 'FIRMADO', 'porcentaje' => 75, 'cantidad_ml' => 250, 'nivel_ayuda' => $indice % 3 === 0 ? 'PARCIAL' : 'INDEPENDIENTE', 'tolerancia' => 'BUENA', 'resultado' => 'REALIZADO', 'observacion' => 'Cuidado realizado y tolerado sin incidencias.']
            );

            $incidente = IncidenteResidente::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'fecha_hora_evento' => now()->subDays(15 - $indice)->startOfDay()->addHours(10)],
                ['cod_turno' => $turno->cod_turno, 'registrado_por' => $enfermero->cod_usu, 'tipo' => $indice % 2 === 0 ? 'CAÍDA_SIN_ALTURA' : 'GOLPE_ACCIDENTAL', 'lugar' => $indice % 2 === 0 ? 'Baño' : 'Habitación', 'actividad_previa' => 'Traslado habitual', 'fue_presenciado' => true, 'testigo' => $enfermero->name, 'descripcion' => 'Evento leve atendido inmediatamente, con valoración inicial y vigilancia posterior.', 'dolor' => 2, 'lesion' => true, 'movilidad_posterior' => 'CONSERVADA', 'cambio_cognitivo' => false, 'medico_informado' => true, 'familiar_informado' => true, 'requiere_seguimiento' => true, 'estado' => 'EN_SEGUIMIENTO', 'responsable_id' => $enfermero->cod_usu, 'seguimiento' => 'Control de dolor, movilidad y estado de piel durante el turno.', 'fecha_seguimiento' => now()->subDays(14 - $indice)]
            );

            $lesion = LesionResidente::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'cod_incidente' => $incidente->cod_incidente],
                ['tipo' => 'CONTUSIÓN', 'zona_corporal' => $indice % 2 === 0 ? 'Rodilla derecha' : 'Antebrazo izquierdo', 'lateralidad' => $indice % 2 === 0 ? 'DERECHA' : 'IZQUIERDA', 'estado' => 'ACTIVA', 'fecha_deteccion' => $incidente->fecha_hora_evento, 'registrado_por' => $enfermero->cod_usu]
            );

            SeguimientoLesion::firstOrCreate(
                ['cod_lesion' => $lesion->cod_lesion, 'fecha_hora_evento' => now()->subDays(14 - $indice)->startOfDay()->addHours(10)],
                ['largo_cm' => 1.5, 'ancho_cm' => 1.0, 'profundidad_cm' => 0, 'es_medible' => true, 'dolor' => 1, 'exudado' => 'AUSENTE', 'piel_circundante' => 'Sin eritema progresivo', 'aspecto' => 'Evolución favorable.', 'accion_realizada' => 'Limpieza, valoración y protección local.', 'observacion' => 'Continuar vigilancia diaria.', 'registrado_por' => $enfermero->cod_usu]
            );

            NotaEvolucionMedica::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'fecha' => now()->subDays($indice)->toDateString(), 'tipo_nota' => 'EVOLUCION'],
                ['hora' => '11:00:00', 'subjetivo' => 'Residente refiere descanso adecuado y tolerancia a la alimentación.', 'objetivo' => 'Consciente, colaborador y hemodinámicamente estable.', 'valoracion' => 'Evolución clínica estable, sin cambios agudos durante la jornada.', 'plan' => 'Continuar tratamiento, hidratación, movilización segura y controles programados.', 'observaciones' => 'Mantener vigilancia según plan individual.', 'pa_sistolica' => 118 + $indice, 'pa_diastolica' => 72 + ($indice % 5), 'fc' => 68 + $indice, 'fr' => 16 + ($indice % 3), 'temperatura' => 36.4 + (($indice % 3) / 10), 'saturacion' => 94 + ($indice % 4), 'registrado_por' => $enfermero->cod_usu, 'estado' => 'ACTIVO']
            );

            PaseTurno::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'turno_saliente_id' => $turno->cod_turno, 'fecha' => now()->subDays($indice + 1)->toDateString()],
                ['turno_entrante_id' => $turnoEntrante->cod_turno, 'enfermero_saliente_id' => $enfermero->cod_usu, 'enfermero_entrante_id' => $enfermeros[($indice + 1) % $enfermeros->count()]->cod_usu, 'estado_general_cierre' => 'ESTABLE', 'resumen_turno' => 'Controles y cuidados realizados según planificación, sin deterioro agudo.', 'tareas_realizadas_json' => ['Control de signos vitales', 'Cuidados básicos'], 'tareas_pendientes_json' => ['Continuar vigilancia clínica'], 'alertas_activas_json' => [], 'recomendacion_siguiente_turno' => 'Revisar tolerancia oral, movilidad y tareas pendientes.', 'requiere_vigilancia_especial' => $indice < 2, 'motivo_vigilancia' => $indice < 2 ? 'Riesgo moderado de caída.' : null, 'estado' => 'GENERADO']
            );
        }
    }
}
