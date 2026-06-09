<?php

namespace Database\Seeders;

use App\Models\AdultoMayor;
use App\Models\AsignacionAdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\Cama;
use App\Models\DocumentoPreadmision;
use App\Models\EstadoAdulto;
use App\Models\Familiar;
use App\Models\Habitacion;
use App\Models\HistorialEstadoAdulto;
use App\Models\Preadmision;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdultoConPreadmisionAprobadaSeeder extends Seeder
{
    // CI único de prueba — no colisiona con otros seeders
    private const CI_ADULTO   = '5544332-SEEDER';
    private const CI_FAMILIAR = '7788991-SEEDER';
    private const HAB_CODIGO  = 'HAB-102-PRUEBA';
    private const CAM_CODIGO  = 'CAM-102-PRUEBA';

    public function run(): void
    {
        // ── 1. Obtener usuario administrador para registros de auditoría ──────────
        $admin = User::firstWhere('correo', 'admincasaamandita@gmail.com')
            ?? User::first();

        // ── 2. Obtener enfermero (preferencia: seeder dedicado, fallback: cualquiera) ──
        $enfermero = User::firstWhere('correo', 'enfermero.prueba@casaamandita.com')
            ?? User::firstWhere('correo', 'enfermero@casaamandita.com')
            ?? User::role('ENFERMEROS')->where('estado', 'ACTIVO')->first();

        if (! $enfermero) {
            $this->command->error('No hay ningún enfermero activo. Ejecute primero EnfermeroConHorarioSeeder o EnfermeriaPacienteSeeder.');
            return;
        }

        // ── 3. Estados del adulto mayor ───────────────────────────────────────────
        $estadoPendiente = EstadoAdulto::where('estado', 'PENDIENTE_VALORACION_INICIAL')->first()
            ?? EstadoAdulto::where('estado', 'ACTIVO')->first();

        $estadoActivo = EstadoAdulto::where('estado', 'ACTIVO')->first();

        if (! $estadoActivo) {
            $this->command->error('No existe el estado ACTIVO en estado_adulto. Ejecute EstadoAdultoSeeder primero.');
            return;
        }

        // ── 4. Turno de enfermería que cubre la hora actual ───────────────────────
        $hora = now()->hour;
        if ($hora >= 6 && $hora < 12) {
            $nombreTurno = 'MAÑANA';
        } elseif ($hora >= 12 && $hora < 18) {
            $nombreTurno = 'TARDE';
        } elseif ($hora >= 18) {
            $nombreTurno = 'NOCHE';
        } else {
            $nombreTurno = 'MADRUGADA';
        }

        $turnoEnfermeria = TurnoEnfermeria::where('nombre', $nombreTurno)->first()
            ?? TurnoEnfermeria::first();

        // ── 5. Habitación y cama ──────────────────────────────────────────────────
        $habitacion = Habitacion::firstWhere('codigo', self::HAB_CODIGO);
        if (! $habitacion) {
            $habitacion = Habitacion::create([
                'codigo'          => self::HAB_CODIGO,
                'nombre'          => 'Habitación 102 — Prueba',
                'tipo_habitacion' => 'INDIVIDUAL',
                'capacidad'       => 1,
                'estado'          => 'DISPONIBLE',
                'observaciones'   => 'Creada por seeder de prueba.',
            ]);
        }

        $cama = Cama::firstWhere('codigo', self::CAM_CODIGO);
        if (! $cama) {
            $cama = Cama::create([
                'cod_habitacion' => $habitacion->cod_habitacion,
                'codigo'         => self::CAM_CODIGO,
                'estado'         => 'DISPONIBLE',
                'observaciones'  => 'Creada por seeder de prueba.',
            ]);
        }

        // ── 6. Familiar responsable ───────────────────────────────────────────────
        $familiar = Familiar::firstWhere('ci', self::CI_FAMILIAR);
        if (! $familiar) {
            $familiar = Familiar::create([
                'nombres'            => 'ROBERTO',
                'ap_paterno'         => 'FLORES',
                'ap_materno'         => 'PAREDES',
                'ci'                 => self::CI_FAMILIAR,
                'parentesco_vinculo' => 'HIJO/A',
                'celular'            => '76543210',
                'correo'             => 'familiar.prueba@correo.com',
                'es_responsable'     => true,
                'estado'             => 'ACTIVO',
                'observaciones'      => 'Familiar creado por seeder de prueba.',
            ]);
        }

        // ── 7. Adulto mayor ───────────────────────────────────────────────────────
        $adulto = AdultoMayor::firstWhere('ci', self::CI_ADULTO);
        if (! $adulto) {
            $adulto = AdultoMayor::create([
                'nombres'                       => 'CARLOS ALBERTO',
                'ap_paterno'                    => 'FLORES',
                'ap_materno'                    => 'MENDOZA',
                'ci'                            => self::CI_ADULTO,
                'expedicion_ci'                 => 'LP',
                'fecha_nac'                     => '1945-03-22',
                'genero'                        => 'MASCULINO',
                'estado_civil'                  => 'VIUDO/A',
                'celular'                       => '70112233',
                'tiene_celular'                 => true,
                'sabe_usar_whatsapp'            => false,
                'departamento_residencia'       => 'LA PAZ',
                'ciudad_municipio'              => 'LA PAZ',
                'zona'                          => 'MIRAFLORES',
                'calle'                         => 'AV. BUSH NRO. 450',
                'fecha_ing'                     => now()->subDays(40)->toDateString(),
                'hora_ing'                      => '09:00:00',
                'tipo_ing'                      => 'REGULAR',
                'permanencia'                   => 'PERMANENTE',
                'nivel_educat'                  => 'SECUNDARIA',
                'grupo_sanguineo'               => 'A+',
                'factor_rh'                     => '+',
                'alergias'                      => 'NINGUNA',
                'seguro_salud'                  => 'SUS',
                'contacto_emergencia_nombre'    => 'ROBERTO FLORES PAREDES',
                'contacto_emergencia_parentesco' => 'HIJO/A',
                'contacto_emergencia_celular'   => $familiar->celular,
                'contacto_emergencia_direccion' => 'MIRAFLORES, LA PAZ',
                'responsable_principal'         => 'ROBERTO FLORES PAREDES',
                'autorizado_informacion_medica' => true,
                'consentimiento_datos'          => true,
                'motivo_ingreso'                => 'CUIDADO_PERMANENTE',
                'procedencia_ingreso'           => 'DOMICILIO_FAMILIAR',
                'observaciones'                 => 'Adulto mayor ingresado desde preadmisión aprobada. Dato de prueba.',
                'cod_est_adul'                  => $estadoActivo->cod_est_adul,
            ]);
        }

        // ── 8. Vincular familiar con adulto ───────────────────────────────────────
        if (! $adulto->familiares()->where('familiares.cod_fam', $familiar->cod_fam)->exists()) {
            $adulto->familiares()->attach($familiar->cod_fam, [
                'parentesco_vinculo' => 'HIJO/A',
                'es_responsable'     => true,
                'estado'             => 'ACTIVO',
                'observaciones'      => 'Vínculo creado por seeder de prueba.',
            ]);
        }

        // ── 9. Preadmisión aprobada ───────────────────────────────────────────────
        $preadmision = Preadmision::firstWhere('ci', self::CI_ADULTO);
        if (! $preadmision) {
            $preadmision = Preadmision::create([
                'estado'                             => 'APROBADA',
                'fecha_solicitud'                    => now()->subDays(45)->toDateString(),
                'fecha_asignacion'                   => now()->subDays(45),
                'nombres'                            => $adulto->nombres,
                'ap_paterno'                         => $adulto->ap_paterno,
                'ap_materno'                         => $adulto->ap_materno,
                'ci'                                 => self::CI_ADULTO,
                'expedicion_ci'                      => 'LP',
                'fecha_nac'                          => $adulto->fecha_nac,
                'genero'                             => $adulto->genero,
                'estado_civil'                       => $adulto->estado_civil,
                'celular'                            => $adulto->celular,
                'departamento_residencia'            => $adulto->departamento_residencia,
                'ciudad_municipio'                   => $adulto->ciudad_municipio,
                'zona'                               => $adulto->zona,
                'calle'                              => $adulto->calle,
                'familiar_nombres'                   => $familiar->nombres,
                'familiar_ap_paterno'                => $familiar->ap_paterno,
                'familiar_ap_materno'                => $familiar->ap_materno,
                'familiar_ci'                        => self::CI_FAMILIAR,
                'familiar_parentesco'                => 'HIJO/A',
                'familiar_celular'                   => $familiar->celular,
                'familiar_correo'                    => $familiar->correo,
                'motivo_ingreso'                     => 'CUIDADO_PERMANENTE',
                'procedencia_ingreso'                => 'DOMICILIO_FAMILIAR',
                'tipo_ingreso'                       => 'REGULAR',
                'permanencia'                        => 'PERMANENTE',
                'prioridad'                          => 'ALTA',
                'descripcion_caso'                   => 'PACIENTE CON DETERIORO COGNITIVO LEVE. REQUIERE SUPERVISIÓN PERMANENTE Y ASISTENCIA EN ACTIVIDADES DE VIDA DIARIA.',
                'documentos_iniciales_completos'     => true,
                'documentos_institucionales_generados' => true,
                'enfermero_asignado'                 => $enfermero->cod_usu,
                'creado_por'                         => $admin->cod_usu,
                'observaciones'                      => 'Preadmisión de prueba generada por seeder. Aprobada y convertida a adulto mayor.',
                'fecha_aprobacion'                   => now()->subDays(40),
                'aprobado_por'                       => $admin->cod_usu,
                'cod_am_generado'                    => $adulto->cod_am,
                'cod_fam_generado'                   => $familiar->cod_fam,
            ]);
        }

        // ── 10. Documentos de preadmisión ─────────────────────────────────────────
        $docsExistentes = DocumentoPreadmision::where('cod_pre', $preadmision->cod_pre)->count();
        if ($docsExistentes === 0) {
            $docsSolicitante = [
                [
                    'tipo_documento'  => 'CI_ADULTO',
                    'nombre_documento' => 'CI del adulto mayor',
                    'grupo_documento' => 'solicitante',
                    'es_institucional' => false,
                    'es_generado_sistema' => false,
                    'bloquea_avance'  => true,
                    'permite_48h'     => false,
                    'obligatorio'     => true,
                    'estado'          => 'RECIBIDO',
                    'observaciones'   => 'Recibido en el proceso de preadmisión.',
                ],
                [
                    'tipo_documento'  => 'CI_FAMILIAR',
                    'nombre_documento' => 'CI del familiar responsable',
                    'grupo_documento' => 'solicitante',
                    'es_institucional' => false,
                    'es_generado_sistema' => false,
                    'bloquea_avance'  => true,
                    'permite_48h'     => false,
                    'obligatorio'     => true,
                    'estado'          => 'RECIBIDO',
                    'observaciones'   => 'Recibido en el proceso de preadmisión.',
                ],
                [
                    'tipo_documento'  => 'SOLICITUD_INGRESO',
                    'nombre_documento' => 'Solicitud inicial de ingreso',
                    'grupo_documento' => 'solicitante',
                    'es_institucional' => false,
                    'es_generado_sistema' => false,
                    'bloquea_avance'  => false,
                    'permite_48h'     => true,
                    'obligatorio'     => true,
                    'estado'          => 'RECIBIDO',
                    'observaciones'   => 'Entregado dentro del plazo de 48 horas.',
                ],
            ];

            $docsInstitucionales = [
                ['tipo_documento' => 'FICHA_PREADMISION',      'nombre_documento' => 'Ficha institucional de preadmisión'],
                ['tipo_documento' => 'AUTORIZACION_VALORACION', 'nombre_documento' => 'Autorización de valoración inicial'],
                ['tipo_documento' => 'CONSENTIMIENTO_DATOS',   'nombre_documento' => 'Consentimiento de tratamiento de datos'],
                ['tipo_documento' => 'ACTA_RECEPCION_DOCUMENTOS', 'nombre_documento' => 'Acta de recepción de documentos'],
            ];

            foreach ($docsSolicitante as $doc) {
                DocumentoPreadmision::create(array_merge($doc, [
                    'cod_pre' => $preadmision->cod_pre,
                ]));
            }

            foreach ($docsInstitucionales as $doc) {
                DocumentoPreadmision::create(array_merge($doc, [
                    'cod_pre'             => $preadmision->cod_pre,
                    'grupo_documento'     => 'institucional',
                    'es_institucional'    => true,
                    'es_generado_sistema' => true,
                    'bloquea_avance'      => false,
                    'permite_48h'         => false,
                    'obligatorio'         => true,
                    'estado'              => 'GENERADO',
                    'fecha_generacion'    => now()->subDays(40),
                    'observaciones'       => 'Generado por el sistema al confirmar la preadmisión.',
                ]));
            }
        }

        // ── 11. Historial de estados ──────────────────────────────────────────────
        $historialExistente = HistorialEstadoAdulto::where('cod_am', $adulto->cod_am)->count();
        if ($historialExistente === 0) {
            // Ingreso: sin estado anterior → PENDIENTE_VALORACION_INICIAL
            HistorialEstadoAdulto::create([
                'cod_am'         => $adulto->cod_am,
                'estado_anterior' => null,
                'estado_nuevo'   => $estadoPendiente->cod_est_adul,
                'fecha_cambio'   => now()->subDays(40),
                'motivo'         => 'Ingreso aprobado desde preadmisión.',
                'cambiado_por'   => $admin->cod_usu,
                'observacion'    => "Preadmisión origen: {$preadmision->cod_pre}",
            ]);

            // Valoración completada: PENDIENTE_VALORACION_INICIAL → ACTIVO
            HistorialEstadoAdulto::create([
                'cod_am'         => $adulto->cod_am,
                'estado_anterior' => $estadoPendiente->cod_est_adul,
                'estado_nuevo'   => $estadoActivo->cod_est_adul,
                'fecha_cambio'   => now()->subDays(38),
                'motivo'         => 'Valoración inicial completada. Adulto mayor dado de alta como residente activo.',
                'cambiado_por'   => $enfermero->cod_usu,
                'observacion'    => 'Valoración de enfermería e inicial médica finalizadas sin observaciones críticas.',
            ]);

            // Seguimiento semanal rutinario
            HistorialEstadoAdulto::create([
                'cod_am'         => $adulto->cod_am,
                'estado_anterior' => $estadoActivo->cod_est_adul,
                'estado_nuevo'   => $estadoActivo->cod_est_adul,
                'fecha_cambio'   => now()->subDays(30),
                'motivo'         => 'Revisión semanal de seguimiento.',
                'cambiado_por'   => $enfermero->cod_usu,
                'observacion'    => 'Paciente estable. Continúa plan de cuidado sin modificaciones.',
            ]);
        }

        // ── 12. Asignación de habitación y cama ───────────────────────────────────
        $asignacion = AsignacionAdultoMayor::where('cod_am', $adulto->cod_am)
            ->where('estado', 'ACTIVO')
            ->first();

        if (! $asignacion) {
            AsignacionAdultoMayor::create([
                'cod_am'           => $adulto->cod_am,
                'cod_habitacion'   => $habitacion->cod_habitacion,
                'cod_cama'         => $cama->cod_cama,
                'fecha_asignacion' => now()->subDays(40)->toDateString(),
                'hora_asignacion'  => '09:30:00',
                'estado'           => 'ACTIVO',
                'observaciones'    => 'Asignación de habitación en el ingreso. Generada por seeder.',
                'registrado_por'   => $admin->cod_usu,
            ]);

            // Actualizar cod_habitacion/cod_cama directamente en adulto_mayor
            $adulto->update([
                'cod_habitacion' => $habitacion->cod_habitacion,
                'cod_cama'       => $cama->cod_cama,
            ]);
        }

        // ── 13. Asignación de turno con enfermero (para hoy) ─────────────────────
        if ($turnoEnfermeria) {
            $asignTurno = AsignacionTurnoAdulto::where('cod_am', $adulto->cod_am)
                ->where('cod_usu_enfermero', $enfermero->cod_usu)
                ->where('estado', 'ACTIVO')
                ->first();

            if (! $asignTurno) {
                AsignacionTurnoAdulto::create([
                    'cod_am'             => $adulto->cod_am,
                    'cod_turno'          => $turnoEnfermeria->cod_turno,
                    'cod_usu_enfermero'  => $enfermero->cod_usu,
                    'cod_habitacion'     => $habitacion->cod_habitacion,
                    'cod_cama'           => $cama->cod_cama,
                    'fecha_inicio'       => now()->toDateString(),
                    'nivel_supervision'  => 'NORMAL',
                    'estado'             => 'ACTIVO',
                    'motivo_asignacion'  => 'RUTINA',
                    'asignado_por'       => $admin->cod_usu,
                ]);
            }
        }

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║         ADULTO MAYOR CON PREADMISIÓN APROBADA        ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info("║  Adulto Mayor  : {$adulto->cod_am} — {$adulto->nombres} {$adulto->ap_paterno}");
        $this->command->info("║  Preadmisión   : {$preadmision->cod_pre} — APROBADA");
        $this->command->info("║  Familiar      : {$familiar->cod_fam} — {$familiar->nombres} {$familiar->ap_paterno}");
        $this->command->info("║  Habitación    : {$habitacion->cod_habitacion} ({$habitacion->codigo})");
        $this->command->info("║  Cama          : {$cama->cod_cama} ({$cama->codigo})");
        $this->command->info("║  Enfermero     : {$enfermero->cod_usu} — {$enfermero->nombres} {$enfermero->ap_paterno}");
        $this->command->info("║  Turno hoy     : {$nombreTurno}");
        $this->command->info("║  Historial     : 3 entradas de estado");
        $this->command->info('╚══════════════════════════════════════════════════════╝');
    }
}
